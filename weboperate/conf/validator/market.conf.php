<?php
return array(
        //营销中心-报价方案设置
        'planprice_add'=>array(
                        array('price_name','require'),
                        array('price_cpid','require'),
                        array('price_stid', 'require'),
                        array('price_base', 'require'),
                        array('price_dot', 'require'),
                        array('price_pversion','require'),
            
                ),
        'productorder'=>array(
                        array('pro_channel_id','require'),
                        array('pro_kh_id','require'),
                        array('pro_cp_id', 'require'),
                        array('pro_price_id', 'require'),
                        array('pro_st_id', 'require'),
                        array('pro_product_area', 'require'),
                        array('pro_product_version', 'require'),
                        array('pro_sell_price', 'require'),
                         array('pro_rebate_price', 'number'),
                        array('pro_real_price', 'require'),
                        array('pro_dot_num', 'require'),
                        array('pro_hire_limit', 'number'),
            
            
                ),
);