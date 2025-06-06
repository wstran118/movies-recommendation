<?php
declare(strict_types=1);

namespace Src;
use PDO;
use PDOException;
class Database {
    private PDO $db;

    public function __construct() {
        try{
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                DB_CONFIG['host'],
                DB_CONFIG['port'],
                DB_CONFIG['dbname']
            );
            $this->db = new PDO($dsn, DB_CONFIG['user'], DB_CONFIG['password']);
        } catch (PDOException $e){
            throw new Exception('Database connection failed: ' . $e->getMessage(), 500);
        }
    }
}


?>