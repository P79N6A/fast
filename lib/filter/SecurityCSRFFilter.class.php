<?php
require_once ROOT_PATH . 'boot/req_inc.php';
require_lib('security/CSRFHandler');
class SecurityCSRFFilter implements IRequestFilter {
    function handle_before(array &$request, array &$response, array &$app) {
        CsrfHandler::validate();
    }
}