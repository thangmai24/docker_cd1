<?php
require_once 'configs/database.php';

abstract class BaseModel {
    // Kết nối database
    protected static $_connection;

    public function __construct() {
        if (!isset(self::$_connection)) {
            self::$_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            if (self::$_connection->connect_errno) {
                die("Kết nối thất bại: " . self::$_connection->connect_error);
            }
        }
    }

    /**
     * Thực thi query
     */
    protected function query(string $sql) {
        return self::$_connection->query($sql);
    }

    /**
     * SELECT statement
     */
    protected function select(string $sql): array {
        $result = $this->query($sql);
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * INSERT statement
     */
    protected function insert(string $sql) {
        return $this->query($sql);
    }

    /**
     * UPDATE statement
     */
    protected function update(string $sql) {
        return $this->query($sql);
    }

    /**
     * DELETE statement
     */
    protected function delete(string $sql) {
        return $this->query($sql);
    }
}
