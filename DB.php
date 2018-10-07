<?php
class DB {

    /**
     * @var PDO
     */
    private static $_pdo;

    private static function setup() {
        self::$_pdo = new PDO(sprintf("mysql:host=%s;dbname=%s", getenv('DB_HOST'), getenv('DB_NAME')),getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        self::$_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return PDO
     */
    public static function pdo() {
        if (self::$_pdo == null)
            self::setup();
        return self::$_pdo;
    }
}