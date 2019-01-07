<?php
class CSRFHandler {
    const TOKEN_NAME = '__es_csrf_t__';
    
    public static function get_token() {
        $csrfToken = CTX()->get_session(CsrfHandler::TOKEN_NAME);
        if ($csrfToken === null) {
            $csrfToken = sha1(uniqid(mt_rand(), true));
            CTX()->set_session(CsrfHandler::TOKEN_NAME, $csrfToken);
        }
        
        return $csrfToken;
    }
    public static function validate() {
        if (self::is_post() && isset($_POST['do']) && $_POST['do'] == 1) {
            $tokenFromSession = CTX()->get_session(CSRFHandler::TOKEN_NAME, true);
            if (! empty($tokenFromSession) && isset($_POST[CSRFHandler::TOKEN_NAME])) {
                $tokenFromPost = $_POST[CsrfHandler::TOKEN_NAME];
                $valid = $tokenFromSession === $tokenFromPost;
            } else {
                $valid = false;
            }
            if (! $valid) {
                throw new Exception('The CSRF token could not be verified.');
            }
        }
    }
    private static  function is_post() {
        return isset($_SERVER['REQUEST_METHOD']) && ! strcasecmp($_SERVER['REQUEST_METHOD'], 'POST');
    }
}