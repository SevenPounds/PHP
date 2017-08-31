<?php

class LogoutAction extends Action {

    public function index() {
        $_SESSION["local_logout"] = true;
        session_destroy();
        $this->redirectToPage(C('SSO_LOGIN_URL'));
    }

    public static function redirectToPage($tourl)
    {
        if (php_sapi_name() === 'cli') {
            @header('Location: '.htmlspecialchars_decode($tourl));
        } else {
            header('Location: '. htmlspecialchars_decode($tourl));
        }
        die();
    }
}