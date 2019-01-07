<?php

require_lib('util/web_util', true);
require_lib('util/taobao_util', true);

class tb_itemcats {

    function do_list(array & $request, array & $response, array & $app) {

    }

    /**
     * 通过类型获取明细 (新框架)
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function dl_tb_itemcats(array & $request, array & $response, array & $app) {

        $app['fmt'] = 'json';
        $ret = load_model('api/TbBaseItemCatsModel')->dl_itemcats();
    }

}
