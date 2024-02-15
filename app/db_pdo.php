<?php

class db_pdo
{
    const DB_SERVER_TYPE = 'mysql'; // MySQL or MariaDB server
    const DB_HOST = '127.0.0.1';    // local server on my laptop
    const DB_PORT = 3307;           // optional, default 3306, use 3307 for MariaDB
    const DB_NAME = 'classicmodels'; // for Database classicmodels
    const DB_CHARSET = 'utf8mb4';  // pour français correct

    const DB_USER_NAME = 'web_site_classic_models';    // if not root it must have been previously created on DB server
    const DB_PASSWORD = '12345678';

    // PDO connection options
    const DB_OPTIONS = [
        // throw exception on SQL errors
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // return records with associative keys only, no numeric index
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    private $DB_connection; //objet connection

    /**
     * constructeur se connecte au serveur
     */
    public function __construct()
    {
        try {
            $dsn = self::DB_SERVER_TYPE . ':host=' . self::DB_HOST . ';port=' . self::DB_PORT . ';dbname=' . self::DB_NAME . ';charset=' . self::DB_CHARSET;
            $this->DB_connection = new PDO($dsn, self::DB_USER_NAME, self::DB_PASSWORD, self::DB_OPTIONS);
            #echo 'connecté à la bd';
        } catch (PDOException $e) {
            header('http/1.0 500 Erreur connection à la bd');
            #Envoyé un e-mail au responsable du site web
            mail("2237036@collegeuniversel.ca", "Probléme site web, connection à la BD", "");
            exit('Erreur connection à la bd' . $e->getMessage());
        }
    }

    /**
     * Se débranche du serveur sql
     */
    public function disconnect()
    {
        $this->DB_connection = null;
    }

    /**
     * Pour requete INSERT, UPDATE, DELETE retourn un PDOStatement
     */
    public function query($sql)
    {
        try {
            $resultat = $this->DB_connection->query($sql);
            return $resultat;
        } catch (PDOException $e) {
            header('http/1.0 500 Erreur requete SQL');
            exit('Erreur requete SQL ' . $e->getMessage()); #termine le programme
        }
    }

    /**
     * Pour requete paramétrée INSERT, UPDATE, DELETE retourn un PDOStatement
     */
    public function queryParam($sql, $params)
    {
        try {
            $stmt = $this->DB_connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            header('http/1.0 500 Erreur requete SQL');
            exit('Erreur requete SQL ' . $e->getMessage()); #termine le programme
        }
    }

    /**
     * Pour requete SELECT retourn un tableau
     */
    public function querySelect($sql)
    {
        $resultat = $this->query($sql);
        $records = $resultat->fetchAll();
        return $records; #retournr un tableau
    }

    /**
     * Pour requete SELECT retourne un tableau
     */
    public function querySelectParam($sql, $params)
    {
        $resultat = $this->queryParam($sql, $params);
        $records = $resultat->fetchAll();
        return $records; #retournr un tableau
    }

    /**
     * retourne la table au complet
     */
    public function table($nom_table)
    {
        return self::querySelect('SELECT * FROM ' . $nom_table);
    }
}
