<?php
    namespace Amin\Event\Classes;
    final class EventRoot{
        private static $instance;
        private $db;
        private function __construct(){
            $this->defineConstants();
            $this->includeClasses();
            session_start();
        }
        private function defineConstants(){
            if(defined('ABSPATH')) return;
            define('ABSPATH', __DIR__.'/../../');
            define('BASE_CONTROLLER_PATH','\Amin\Event\Controllers');
            //echo ABSPATH;
        }
        private function includeClasses(){
            require_once ABSPATH.'app/Helpers/Env.php';
            \Amin\Event\Helpers\Env::load(ABSPATH.'.env');
            require_once ABSPATH.'app/Classes/DBQuery.php';
            $this->db = new \Amin\Event\Classes\DBQuery();
            require_once ABSPATH.'routes/web.php';
        }
        public function getDB() {
            return $this->db;
        }
        public static function getInstance(){
            if(is_null(self::$instance)){
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
?>