<?php
echo header('Content-Type: text/html; charset=utf-8');

class SQlite
{
    static protected $_conn                 =   null;

    public static function getConn()
    {
        if (self::$_conn === null) {
            self::$_conn = sqlite_popen('/home/Shlomin/db/shlomin.sqlite', 0666, $err);
            if (self::$_conn === false)
                die($err);
        }
        return self::$_conn;
    }

    public static function createTables()
    {
        $query = 'DROP TABLE "categories"';
        sqlite_query($query, SQlite::getConn());
        $query = 'CREATE TABLE "categories" ("order" INTEGER NOT NULL , "folder" BOOL NOT NULL  DEFAULT 0, "parent" VARCHAR NOT NULL , "name" VARCHAR NOT NULL )';
        sqlite_exec($query, SQlite::getConn());
        $query = 'CREATE INDEX "order" ON "categories" ("order")';
        sqlite_exec($query, SQlite::getConn());
        $query = 'CREATE INDEX "parent" ON "categories" ("parent")';
        sqlite_exec($query, SQlite::getConn());
        $query = 'CREATE INDEX "folder" ON "categories" ("folder")';
        sqlite_exec($query, SQlite::getConn());

        $query = 'DROP TABLE "articles"';
        sqlite_exec($query, SQlite::getConn());
        $query = 'CREATE TABLE "articles" ("category" INTEGER PRIMARY KEY, "headline" TEXT default "", "content" TEXT, "date" INTEGER)';
        sqlite_exec($query, SQlite::getConn());
    }
}

SQlite::createTables();
?>