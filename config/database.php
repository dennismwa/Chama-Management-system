<?php
/**
 * Chama Management Platform - Database Configuration
 * 
 * Secure database connection with error handling and environment configuration
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('CHAMA_ACCESS')) {
    die('Direct access denied');
}

class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;
    private $charset;
    
    /**
     * Database configuration
     */
    private function __construct() {
        // Database credentials from requirements
        $this->host = 'localhost'; // Replace with your hosted domain
        $this->username = 'vxjtgclw_Chamagroup';
        $this->database = 'vxjtgclw_Chama Group';
        $this->password = 'K)!Uj@_qFArf8DXE';
        $this->charset = 'utf8mb4';
        
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_TIMEOUT            => 30,
                PDO::ATTR_PERSISTENT         => false
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Set timezone
            $this->connection->exec("SET time_zone = '+03:00'"); // East Africa Time
            
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            
            // In production, show generic error
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
                die("Database connection failed. Please try again later.");
            } else {
                die("Database Error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        // Check if connection is still alive
        if ($this->connection === null) {
            $this->connect();
        }
        
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute prepared statement with parameters
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sql);
            throw new Exception("Database operation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get single record*/
    public function fetchOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get all records
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single value
     */
    public function fetchValue($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Check if in transaction
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Escape string for safe usage
     */
    public function quote($string) {
        return $this->connection->quote($string);
    }
    
    /**
     * Get database info
     */
    public function getInfo() {
        return [
            'server_version' => $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $this->connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'connection_status' => $this->connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'server_info' => $this->connection->getAttribute(PDO::ATTR_SERVER_INFO)
        ];
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->connection = null;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Global database helper functions
 */
function db() {
    return Database::getInstance();
}

function dbConnection() {
    return Database::getInstance()->getConnection();
}
?>