<?php
namespace Amin\Event\Classes;
class SessionManagement{
    private static $user_request_data = null;
    public function startSession(){
        session_start();
    }
    public static function setUserRequestSession($value){
        $_SESSION[self::$user_request_data] = $value;
    }
    public static function request(){
        return $_SESSION[self::$user_request_data];
    }
}