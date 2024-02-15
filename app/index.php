<?php
session_start();

#Indicateur que le client passe par ce fichier
$index_loaded = true;

require_once 'globals.php';
require_once 'outils.php';
require_once 'picture_upload_functions.php';
require_once 'view/webpage.php';
require_once 'db_pdo.php';

require_once 'users.php';
require_once 'customers.php';
require_once 'employees.php';

//Controller
function main()
{
    #Lecture cope opératoin désirée
    #exemple index.php?op=0 pour page acceuil
    if (isset($_REQUEST['op'])) {
        $op = $_REQUEST['op'];
    } else {
        $op = 0; #par défaut page acceuil
    }
    switch ($op) {
        case 0:
            #Page d'acceuil
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if ($lang == 'en') {
                $msg = 'This is the home page';
                $desc = "
                The largest selection of scale models - Cars - Trucks - Airplanes - Motorcycles and more";
            } elseif ($lang == 'fr') {
                $msg = '<h1>Ceci est la page d\'acceuil</h1>';
                $desc = 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus';
            } else {
                $msg = 'Choisir français ou anglais:';
                $desc = 'Select prefered language - SÉlectionner votre langue';
            }

            #Renvoie le nombre de vue
            $nb_count = webpage::viewCount("log/home_view_count.log");
            #Enregistre la date et l'heure de la visite et l'adresse ip(Exercice 10-3 et 15-1)
            $date_visit = date(DATE_RFC2822, time()) . "\n";
            file_put_contents("log/visitors.log", $_SERVER['REMOTE_ADDR'] . ' ' . $date_visit, FILE_APPEND);

            $pageData = [
                'title' => 'ClassicModels.com - Acceuil',
                'description' => $desc,
                'page_content' => $msg,
                'view_count' => $nb_count
            ];

            webpage::render($pageData);
            break;

        case 1:
            #Appeler page login
            $pageData = [
                'title' => 'ClassicModels.com - Login',
                'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                'page_content' => '<h1>Connectez-vous</h1>' . users::login() . '</br>' //
            ];

            webpage::render($pageData);
            break;
        case 2:
            #Valider formulaire de login
            $pageData = [
                'title' => 'ClassicModels.com - Login',
                'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                //'page_content' => $users . '</br>' //'<h1>Ceci est la page de login</h1>'
            ];

            if (users::loginVerify()) {
                $pageData['page_content'] = 'Vous êtes connecté' . $_POST['email'] . '!';
                if (!isset($_COOKIE['last_visit'])) {
                    setcookie('last_visit', date(DATE_RFC2822, time()), time() + (5 * 365 * 24 * 60 * 60));
                    $pageData['page_content'] .= '</br>Binvenue c\'est votre première visite';
                } else {
                    $pageData['page_content'] .= '</br>Rebienvenue, votre dernière visite était le : ' . $_COOKIE['last_visit'];
                }
            } else {
                $pageData['page_content'] = 'Email ou mot de passe invalide</br>' . users::login() . '</br>';
            }
            webpage::render($pageData);

            break;

        case 3:
            #Appeler page register
            $pageData = [
                'title' => 'ClassicModels.com - Login',
                'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                'page_content' => users::register()
            ];

            webpage::render($pageData);
            break;

        case 4:
            #Valider les informations du formulaire d'inscription
            $pageData = [
                'title' => 'ClassicModels.com - Login',
                'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                'page_content' => users::registerVerifiy()
            ];

            webpage::render($pageData);

            break;

        case 5:
            users::logout();
            break;

        case 10:
            #Page contactez-nous
            $nb_count = webpage::viewCount("log/contact_view_count.log");
            $pageData = [
                'title' => 'ClassicModels.com - Contact',
                'description' => 'Contactez nous pour plus d\'information sur nos produit',
                'page_content' =>
                '<h1>Contactez-nous</h1>
                <form action methode="POST">
                    Nom : <input type="text" name = "nom">
                    <input type = "submit" name = "Envoyer" value = "Envoyer" />
                </form>
                ',
                'view_count' => $nb_count
            ];

            webpage::render($pageData);
            break;


            #DÉBUT SECTION Examen FINAL PRATIQUE - Classicmodel - Opération CRUD sur la table users

            #Afficher la liste des utilisateur
        case 20:
            if ($_SESSION['email']) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => '<div class="users-list">' . users::list() . '</div>'
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

            #Supprimer un utilisateur
        case 24:
            if ($_SESSION['email']) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => users::delete()
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

            #Téléverser un fichier
        case 50:
            #Téléverser (download) un fichier PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="un_fichier.pdf"');
            readfile('un_fichier.pdf');
            break;

        case 51:
            #redirige le client vers le site http://www.timhortons.ca
            header('location: http://www.timhortons.ca');
            break;

            #DÉBUT SECTION PROJET FINAL PRATIQUE - Classicmodel - Opération CRUD sur la table EMPLOYEES

        case 300:
            if (isset($_SESSION['email'])) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => '<div class="employee-list">' . employees::list() . '</div>'
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

        case 301:
            if (isset($_SESSION['email'])) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => '<div class="employee-details">' . employees::details() . '</div>'
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

        case 302:
            if (isset($_SESSION['email'])) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => '<div class="employee-form">' . employees::form() . '</div>'
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

        case 303:
            if (isset($_SESSION['email'])) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => employees::save()
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

        case 304:
            if (isset($_SESSION['email'])) {
                $pageData = [
                    'title' => 'ClassicModels.com - Employee',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' => employees::delete()
                ];

                webpage::render($pageData);
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

        case 320:
            #Service web retourne liste des employées en format json
            if (isset($_SESSION['email'])) {
                employees::listJSON();
            } else {
                crash(401, "Vous devez être connecter");
            }

            break;

            #FIN SECTION PROJET FINAL PRATIQUE - Classicmodel - Opération CRUD sur la table EMPLOYEES

        case 400:
            if (isset($_SESSION['email'])) {
                #Valider les informations du formulaire d'inscription
                if (!isset($_POST['id'])) {
                    $list = customers::list();
                } else {
                    $id = $_POST['id'];
                    $id = htmlspecialchars($id);
                    $list = customers::list($id);
                }
                $pageData = [
                    'title' => 'ClassicModels.com - Login',
                    'description' => 'Le plus vaste de choix de modèles réduits - Voitures - Camions - Avions - Motos et plus',
                    'page_content' =>
                    $_SESSION['email'] .
                        '<div><h3>Custumers List</h3></div>' .
                        '<div>Number of customers found : ' . count($list) . '</div></br>' .
                        search(400) . '' .
                        tableHTML_LIST($list)
                ];

                webpage::render($pageData);
            } else {
                header("HTTP/1.0 401 vous devez être connecté");
                exit("vous devez être connecté");
            }

            break;

        case 420:
            #Service web retourne liste des clients en format json
            customers::listJSON();
            break;

        default:
            #Réponse avec le code HTTP 400 et message personalisé
            crash(400, "ce code d'opération est inconnu");
            break;
    }
}

//Demarrer   le programme
main();
