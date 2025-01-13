<?php
class Database {
    private $conn;
    
    public function __construct() {
        if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
        if (!defined('DB_NAME')) define('DB_NAME', 'restaurant_reservation'); 
        if (!defined('DB_USER')) define('DB_USER', 'root');
        if (!defined('DB_PASS')) define('DB_PASS', '');

        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error");
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}