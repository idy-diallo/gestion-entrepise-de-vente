<?php

class webpage
{
    //Constructeur
    function __construct()
    {
        #Appel constructeur class parent au besoin
        #parent::__construct();
    }

    function __destruct()
    {
    }

    /**
     * returne le nombre de vu d'une page. Le nom du fichier
     * contenant le compte précédent est reçu en paramètre
     */
    public static function viewCount($filename)
    {
        if (file_exists($filename)) {
            $count = file_get_contents($filename);
            $count++;
        } else {
            $count = 0;
        }
        file_put_contents($filename, $count);
        return $count;
    }

    /**
     * Envoi page web au client, $pageData contient le titre, la description 
     */
    public static function render($pageData)
    {
        global $index_loaded;
        #HEAD
        require_once 'view\head.php';

        #HEADER
        require_once 'view\header.php';

        #BARRE DE NAVIGATION
        require_once 'view\nav.php';

        #CONTENT
        echo $pageData['page_content'];

        #FOOTER
        require_once 'view\footer.php';
    }
}
