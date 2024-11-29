<?php
class ConnectionDb
{

    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $dbName = 'recruitmentdb';

    public  static $conn;

    // create method
    public  static function DbConnection()
    {
        self::$conn = new mysqli(self::$host, self::$username, self::$password, self::$dbName);

        // check if we have error
        if (self::$conn->connect_error) {
            echo "CONNECTION PROBLEM";
        }
        return self::$conn;
    }
}
