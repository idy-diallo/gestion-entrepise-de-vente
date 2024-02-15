<?php

class customers
{
    public static function list($id = '')
    {
        $DB = new db_pdo();
        if ($id == '') {
            $sql = "SELECT id, name, country FROM customers";
            $list = $DB->querySelect($sql);
        } else {
            $sql = "SELECT id, name, country FROM customers WHERE id = '$id'";
            $list = $DB->querySelect($sql); //, [$id]
        }
        return $list;
    }

    /**
     * Envoit au client la liste des clients de la compagnie en format JSON
     */
    public static function listJSON()
    {
        $DB = new db_pdo();
        $customers = $DB->table("customers");
        $customersJSON = json_encode($customers, JSON_PRETTY_PRINT);
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(200);
        echo $customersJSON;
    }
}
