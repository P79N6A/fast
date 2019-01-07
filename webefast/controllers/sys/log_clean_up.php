<?php
require_lib ('util/web_util', true);

class log_clean_up {
    public function doCleanUp(array & $request, array & $response, array & $app)
    {
        load_model('sys/LogCleanUpModel')->allCleanUp();
        $response['status'] = 1;
    }
}
