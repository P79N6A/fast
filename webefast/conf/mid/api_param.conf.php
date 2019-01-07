<?php

return array(
    /**
     * mes接口对接
     * 日丰项目用
     */
    'mes' => array(
        /**
         * 接口配置对照
         */
        'config' => array(
            'param_value1' => array(
                'key' => 'api_url',
                'name' => '接口地址',
            ),
            'param_value2' => array(
                'key' => 'username',
                'name' => '用户名',
            ),
            'param_value3' => array(
                'key' => 'password',
                'name' => '密码',
            )
        ),
       
    ),
    
        /**
     * bserp接口对接
     * 
     */
    'bserp2' => array(
        /**
         * 接口配置对照
         */
        'config' => array(
            'param_value1' => array(
                'key' => 'connection_mode',
                'name' => '网络单据对接模式',
            ),
            'param_value2' => array(
                'key' => 'api_url',
                'name' => '接口地址',
            ),

            'param_value3' => array(
                'key' => 'api_secret',
                'name' => '密钥',
            )

        ),
     
    )
);
