<?php

class users
{
    //Retourner un formulaire de connexion
    public static function login()
    {
        if (isset($_SESSION['login_compte']) and $_SESSION['login_compte'] >= LOGIN_MAX_ESSAI) {
            return 'Le nombre max de tentative atteint réessayer plutard';
        }
        return <<< HTML
            <form action="index.php?op=2" method="POST">
                <h3>Connectez-vous</h3>
                <input type="hidden" name="form_id" value="login_form" />
                <div>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" maxlength="126" required placeholder="Email max 126 caractères" autofocus/>*
                </div>
                <div>
                    <label for="pw">Mot de passe</label>
                    <input type="password" name="pw" id="pw" maxlength="8" required placeholder="max 8 caractères" />*
                </div>
                <div>
                    <input type="submit" value="Continuez" />
                    <button type="button" onclick="history.back();">Annuler</button>
                </div>
            </form>
        HTML;
    }

    //Vérifier login
    public static function loginVerify()
    {
        if (!isset($_SESSION['login_compte'])) {
            $_SESSION['login_compte'] = 1;
        } else {
            $_SESSION['login_compte']++;
        }
        if ($_SESSION['login_compte'] > LOGIN_MAX_ESSAI) {
            return 'Le nombre max de tentative atteint réessayer plutard';
        }
        #Vérifier qu'un formulaire de login à été transmit
        if (!isset($_REQUEST['form_id']) || $_REQUEST['form_id'] !== 'login_form') {
            crash(400, 'Mauvais formulaire envoyé à fonction loginVerify()');
        }
        if (!isset($_POST['email'])) {
            crash(400, 'erreur email manquant dans le formulaire');
        } else {
            $email = $_POST['email'];
            $email = htmlspecialchars($email);
            if (strlen($email) > 126) {
                crash(400, 'erreur taille email max 126 char');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                crash(400, 'erreur email invalide');
            }
        }

        if (!isset($_POST['pw'])) {
            crash(400, 'erreur mot de passe manquant dans le formulaire');
        } else {
            $pw = $_POST['pw'];
            $pw = htmlspecialchars($pw);
            if (strlen($pw) > 8) {
                crash(400, 'erreur taille mot de passw max 8 char');
            }
        }
        //Envoit requete SQL à la bd
        $DB = new db_pdo(); #objet connexion
        $sql = "SELECT * FROM users WHERE email = ?";
        $users = $DB->querySelectParam($sql, [$email]);
        if (count($users) == 1 and password_verify($pw, $users[0]['pw'])) {
            $_SESSION['email'] = $email; #se souvenir de l'usager
            $_SESSION['user_picture'] = $users[0]['picture']; #se souvenir de l'usager

            #Pour afficher la date et l'heure que l'usager c'est connecter
            file_put_contents("log/login.log", $email . ' login on ' . date(DATE_RFC822) . "\n", FILE_APPEND);
            return true;
        }

        #OU
        #$users = $DB->table("users");

        // var_dump($users);

        // $users = [
        //     ['id' => 0, 'email' => 'Yannick@gmail.com', 'pw' => '12345678'],
        //     ['id' => 1, 'email' => 'Victor@test.com', 'pw' => '11111111'],
        //     ['id' => 2, 'email' => 'Christian@victoire.ca', 'pw' => '22222222'],
        // ];

        // $login_verify = false;
        // foreach ($users as $user) {
        //     if ($user['email'] == $email && $user['pw'] == $pw) {
        //         $login_verify = true;
        //         $_SESSION['email'] = $email; #se souvenir de l'usager
        //         break;
        //     }
        // }
        //return $login_verify;
    }

    //Retourner un formulaire d'inscription
    public static function register($msg = '', $preData = [])
    {
        $pays = [
            [1, 'CA', 'Canada'],
            [2, 'US', 'États-Unis'],
            [3, 'MX', 'Mexique'],
            [4, 'FR', 'France'],
            [5, 'AU', 'Autre']
        ];

        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $DB = new db_pdo();
            $user = $DB->querySelect("SELECT * FROM users WHERE id = '$id'");
            foreach ($user as $value) {
                $preData['fullname'] = $value['fullname'];
                $preData['email'] = $value['email'];
                $preData['language'] = $value['language'];
                $preData['spam_ok'] = $value['spam_ok'];
                if ($value['country']) {
                    $preData['country'] = $value['country'];
                } else {
                    $preData['country'] = '';
                }
            }
        } else if ($preData == []) {
            $DB = new db_pdo();
            $result = $DB->querySelect("SELECT MAX(id) as 'max' FROM users");
            $new_id = $result[0]['max'] + 1;
            $preData = [
                'fullname' => '',
                'email' => '',
                'language' => 'fr',
                'pw' => '',
                'pw2' => '',
                'spam_ok' => true,
                'country' => 'CA'
            ];
        }
        $variable = $msg . '<form enctype="multipart/form-data" action="index.php?op=4" method="POST">
                <h3>Inscrire/Modifier un utilisateur</h3>
                <div>
                    <!-- <label for="fullname">Fullname</label> -->
                    <input type="fullname" name="fullname" id="fullname" maxlength="50" placeholder="nom et prenom" value="' . $preData['fullname'] . '" required />
                </div>
                <div>
                    <select name="country" required>';
        foreach ($pays as $un_pays) {
            if ($un_pays[1] == $preData['country']) {
                $variable .= '<option value="' . $un_pays[1] . '" selected>' . $un_pays[2] . '</option>';
            } else {
                $variable .= '<option value="' . $un_pays[1] . '">' . $un_pays[2] . '</option>';
            }
        }

        $variable .= '</select>
                </div>
                <div>
                    <div>Sélectionner votre langue</div>';
        if ($preData['language'] == 'fr') {
            $variable .= '<div>
                        <input type="radio" name="language" value="fr" id="fr" maxlength="25" checked />
                        <label for="fr">Français</label>
                    </div>
                    <div>
                        <input type="radio" name="language" value="en" id="en" maxlength="25" />
                        <label for="en">English</label>
                    </div>
                    <div>
                        <input type="radio" name="language" value="autre" id="autre" maxlength="25" />
                        <label for="autre">Autre</label>
                    </div>
                    ';
        } else if ($preData['language'] == 'en') {
            $variable .= '<div>
                        <input type="radio" name="language" value="fr" id="fr" maxlength="25" />
                        <label for="fr">Français</label>
                    </div>
                    <div>
                        <input type="radio" name="language" value="en" id="en" maxlength="25" checked />
                        <label for="en">English</label>
                    </div>
                    <div>
                        <input type="radio" name="language" value="autre" id="autre" maxlength="25" />
                        <label for="autre">Autre</label>
                    </div>
                    ';
        } else {
            $variable .= '<div>
                        <input type="radio" name="language" value="fr" id="fr" maxlength="25" />
                        <label for="fr">Français</label>
                    </div>
                    <div>
                        <input type="radio" name="language" value="en" id="en" maxlength="25" />
                        <label for="en">English</label>
                    </div>
                    <div>
                        <input type="radio" name="language" value="autre" id="autre" maxlength="25" checked />
                        <label for="autre">Autre</label>
                    </div>
                    ';
        }


        $variable .= '</div>
                <div>
                    <!-- <label for="email">Email</label> -->
                    <input type="email" name="email" id="email" maxlength="126" placeholder="courriel" value="' . $preData['email'] . '" required />
                </div>';
        if (!isset($_REQUEST['id'])) {
            $variable .= '<div>
                    <!-- <label for="pw">Mot de passe</label> -->
                    <input type="password" name="pw" id="pw" maxlength="8" placeholder="mot de passe max 8 char" require_once />
                </div>
                <div>
                    <!-- <label for="pw">Mot de passe</label> -->
                    <input type="password" name="pw2" id="pw" maxlength="8" placeholder="répétez le mot de passe" required />
                </div>';
        }

        $variable .= '<div>
                    <input type="checkbox" name="spam_ok" value="1" checked id="spam_ok" maxlength="8" />
                    <label for="spam_ok">Je désire recevoirpériodiquement des informations au sujet des produits...</label>
                </div>
                
                </br>
                <div>
                    <label>Choisir votre photo</label>
                    <input type="file" name="ma_photo" />
                </div>

                <div>
                    <input type="submit" value="Continuez" />
                </div>
            </form>
        ';
        return $variable;
    }

    public static function registerVerifiy()
    {
        $msg = '';

        // $users = [
        //     ['id' => 0, 'email' => 'Yannick@gmail.com', 'pw' => '12345678'],
        //     ['id' => 1, 'email' => 'Victor@test.com', 'pw' => '11111111'],
        //     ['id' => 2, 'email' => 'Christian@victoire.ca', 'pw' => '22222222'],
        // ];

        #Verifier le nom complet
        if (!isset($_POST['fullname'])) {
            crash(400, 'prénom et nom manquant dans le formulaire');
        } else {
            $fullname = $_POST['fullname'];
            $fullname = htmlspecialchars($fullname);
            if (strlen($fullname) == 0 || strlen($fullname) > 50) {
                crash(400, 'erreur taille nom complet max 50 char');
            }
        }

        #Récupérer le pays
        if (!isset($_POST['country'])) {
            crash(400, 'erreur pays manquant dans le formulaire');
        } else {
            $country = $_POST['country'];
        }

        #Récupérer la langue
        if (!isset($_POST['language'])) {
            crash(400, 'erreur langue manquant dans le formulaire');
        } else {
            $language = $_POST['language'];
        }

        #Récupérer le spam
        if (!isset($_POST['spam_ok'])) {
            $spam_ok = 0;
        } else {
            $spam_ok = 1;
        }

        #Vérifier le couriel
        if (!isset($_POST['email'])) {
            crash(400, 'erreur email manquant dans le formulaire');
        } else {
            $email = $_POST['email'];
            $email = htmlspecialchars($email);
            if (strlen($email) > 126) {
                crash(400, 'erreur taille email max 126 char');
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                crash(400, 'erreur email invalide');
            } else {
                #Vérifier si le courriel existe
                //global $users;
                $DB = new db_pdo();
                $sql = "SELECT * FROM users WHERE email = ?";
                $users = $DB->querySelectParam($sql, [$email]);
                if (count($users) == 1) {
                    $msg = 'Ce email est déjà utilisé, svp. choisir un autre email ou login';
                }
                // foreach ($users as $user) {
                //     if ($email === $user['email']) {
                //         $msg = 'Ce email est déjà utilisé, svp. choisir un autre email ou login';
                //     }
                // }
            }
        }

        if (!isset($_POST['pw'])) {
            crash(400, 'erreur mot de passe manquant dans le formulaire');
        } else {
            $pw = $_POST['pw'];
            $pw = htmlspecialchars($pw);
            if (strlen($pw) > 8) {
                crash(400, 'erreur taille mot de passe max 8 char');
            }
        }

        if (!isset($_POST['pw2'])) {
            crash(400, 'erreur mot de passe manquant dans le formulaire');
        } else {
            $pw2 = $_POST['pw2'];
            $pw2 = htmlspecialchars($pw2);
            if (strlen($pw2) > 8) {
                crash(400, 'erreur taille mot de passw max 8 char');
            }
        }

        if ($pw !== $pw2) {
            $msg = 'Les deux mots de passe ne sont pas identiques';
        }

        #Vérifier une image
        $msg_photho = Picture_Uploaded_Is_Valid('ma_photo');
        if ($msg_photho !== 'OK') {
            $photo = $msg_photho;
        } else {
            $msg .= $msg_photho;
        }

        if ($msg != '') {
            return self::register($msg, $_REQUEST);
        } else {
            #insérer dans la base de données
            $_SESSION['email'] = $email;

            $pw_encoding = password_hash($pw, PASSWORD_DEFAULT); #Encrypte le mot de passe

            $DB = new db_pdo();

            $params = [$email, $pw_encoding, $fullname, $country, $language, $spam_ok, $_FILES['ma_photo']['name']];

            if (isset($_REQUEST['id'])) {
                $id = $_REQUEST['id'];

                $result = $DB->querySelectParam("SELECT * FROM users WHERE id = ?", [$id]);
                if (count($result) == 1) {
                    $sql = "UPDATE users SET email = ?, pw_encoding = ?, fullname = ?, country = ?, spam_ok = ?, picture = ? WHERE id = ?";
                    $result = $DB->queryParam($sql, $params);

                    #Sauvegarder la photo sélectionnée
                    $msg_photho = Picture_Uploaded_Save_File('ma_photo', 'user_images/');

                    return "Utilisateur Modifié avec succée";
                } else {
                    crash(400, "Erreur usager introuvable, pas effacé");
                }
            } else {
                $sql = "INSERT INTO users (email, pw, fullname, country, language, spam_ok, picture) VALUES(?, ?, ?, ?, ?, ?, ?)";
                $result = $DB->queryParam($sql, $params);

                #Sauvegarder la photo sélectionnée
                $msg_photho = Picture_Uploaded_Save_File('ma_photo', 'user_images/');

                return "Utilisateur Ajouté avec succée";
            }
        }
    }

    /**
     * Retourner la liste des utilisateurs ou d'un utilisateur selon son id
     */

    public static function list($id = '')
    {
        $DB = new db_pdo();

        if (!isset($_POST['id'])) {
            $list = $DB->querySelect("SELECT id, fullname, email , picture FROM users");
        } else {
            $id = $_POST['id'];
            $sql = "SELECT id, fullname, email , picture FROM users WHERE id = ?";
            $list = $DB->querySelectParam($sql, [$id]);
        }
        //echo 'id = ' . $id;
        $html = '<h3>Liste des Utilisateurs</h3>';
        //$html .= '<div>Le nombre d\'utilisateur est : ' . count($list);
        $html .= '<a href="index.php?op=3&action=ajouter">+ Ajouter un utilisateur</a>';
        $html .= search(20);
        $html .= tableHTML_LIST($list, '#', 3, 24);

        return $html;
    }

    /**
     * Supprime un utilisateur
     */
    public static function delete()
    {
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];

            $DB = new db_pdo();
            $result = $DB->querySelectParam("SELECT * FROM users WHERE id = ?", [$id]);
            if (count($result) == 1) {
                $result = $DB->queryParam("DELETE FROM users WHERE id = ?", [$id]);
                return "<div>OK usager effacé</div>" . self::list();
            } else {
                crash(400, "Erreur usager introuvable, pas effacé");
            }
        }
    }

    /**
     * Déconnecte un utilisateur
     */
    public static function logout()
    {
        #Pour afficher la date et l'heure que l'usager c'est déconnecter
        file_put_contents("log/login.log", $_SESSION['email'] . ' logout on ' . date(DATE_RFC822) . "\n", FILE_APPEND);

        $_SESSION['email'] = null;
        $_SESSION['login_compte'] =  null;
        $_SESSION['user_picture'] = null;
        #redirige le client vers la page d'acceuil
        header('location: http://w12-exercices/serveur/index.php');
    }
}
