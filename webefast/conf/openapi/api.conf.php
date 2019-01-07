<?php
return array(
        // 对外开放的接口，只有配置在这里方法才会对外开放调用。
        'api' => array(
                'open/OpenModel' => array(
                        'proxy'             => '',
                ),
        ),
        // 设置别名，可以根据接口别名路由到对应的model方法。
        'alias' => array(               
                'open'                               =>  'open/OpenModel::proxy',
        )
);