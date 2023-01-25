<?php


namespace DBUtils;


use Doctrine\DBAL\Connection;

class FileDB
{
    private Connection $connection;
    public static function initializeDB(string $filename): void
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}
