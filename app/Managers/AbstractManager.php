<?php
namespace app\Managers;

use PDO;
use PDOException;

abstract class AbstractManager
{
    protected PDO $db;

    public function __construct()
    {
        $host = '127.0.0.1'; // important
        $port = 8889; // port MySQL MAMP
        $dbname = 'agriMai';
        $charset = 'utf8mb4';
        $user = 'root';
        $password = 'root';

        $dsn = "mysql:host=$host;port=$port;charset=$charset;dbname=$dbname";

        try {
            $this->db = new PDO($dsn, $user, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base : " . $e->getMessage());
        }
    }
}