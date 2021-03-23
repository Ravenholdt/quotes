<?php
namespace Loek;
use PDO;

class DB {

    /**
     * @var PDO
     */
    private static $_pdo;

    private static function setup() {
        self::$_pdo = new PDO(
            sprintf("mysql:host=%s;dbname=%s", getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
        );
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