<?php 
    namespace Amin\Event\Classes;
    class DB extends Database {
        public function __construct() {
            Database::getInstance();
        }
        public static function beginTransaction()
        {
            self::getConnection()->beginTransaction();
        }
        public static function commit()
        {
            self::getConnection()->commit();
        }
        public static function rollback()
        {
            self::getConnection()->rollback();
        }
        public static function rawQuery($sql,$values){
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($values);
            return $stmt;
        }
    }
?>