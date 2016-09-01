<?php
namespace core;
class Session {
    
    private static $session;
    
    /**
     * 初始化的时候判断session有没有开启
     */
    private function __construct() {
        session_status() === PHP_SESSION_DISABLED ? session_start() : '';
    }
    
    private function __clone() {}
    
    /**
     * 返回session实例
     * @return Session
     */
    public static function getInstance() {
        if (!self::$session instanceof self) {
            self::$session = new self;
        }
        return self::$session;
    }
    
    /**
     * 设置session变量
     * @param string $key
     * @param mixed $val
     */
    public function set($key, $val) {
        $_SESSION[$key] = $val;
    }
    
    /**
     * 获取session变量
     * @param string $key
     * @return boolean|mixed
     */
    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }
    
    /**
     * 销毁session
     */
    public function destroy() {
        session_destroy();
    }
}