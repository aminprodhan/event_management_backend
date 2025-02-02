<?php
    namespace Amin\Event\Classes;
    class Database{    
        private static $instance = null;
        protected static $conn = null;
        private function __construct($class=null){
            try {
                //$this->class=$class;
                $dsn = "mysql:host=".getenv('DB_HOST').";dbname=".getenv('DB_DATABASE').";charset=UTF8";
                //echo $dsn;
                $options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
	            self::$conn = new \PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $options);
                self::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                self::$conn->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
              } catch(\PDOException $e) {
                    echo "Connection failed: " . $e->getMessage();
              }
        }
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self(); // Create the instance
            }
            return self::$instance;
        }
        public static function getConnection() {
            return self::$conn;
        }
        public function closeConnection() {
            self::$conn = null;
         }   
    }

?>