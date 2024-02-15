<?php

class employees
{
    /**
     * Retourner la liste des employees ou d'un employee selon son id
     */

    public static function list($id = '')
    {
        $DB = new db_pdo();

        if (!isset($_POST['id'])) {
            $list = $DB->table("employees");
        } else {
            $id = $_POST['id'];
            $sql = "SELECT * FROM employees WHERE id = ?";
            $list = $DB->querySelectParam($sql, [$id]);
        }
        //echo 'id = ' . $id;
        $html = '<h3>Liste des employees</h3>';
        //$html .= '<div>Le nombre d\'employees est : ' . count($list);
        $html .= '<a href="index.php?op=302&action=ajouter">+ Ajouter un employée</a>';
        $html .= search(300);
        $html .= tableHTML_LIST($list, 301, 302, 304);

        return $html;
    }

    /**
     * Voir les informations d'un employee
     */

    public static function details()
    {
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $DB = new db_pdo();
            $employee = $DB->querySelectParam("SELECT * FROM employees WHERE id = ?", [$id]);

            return '<h2>Detail</h2>
            <div>numéro : ' . $id . '</div>
            <div>prénom : ' . $employee[0]['firstName'] . '</div>
            <div>nom : ' . $employee[0]['lastName'] . '</div>
            <div>bureau : ' . $employee[0]['officeId'] . '</div>
            <div>titre : ' . $employee[0]['jobTitle'] . '</div></br>
            <span><a href="index.php?op=302&id=' . $id . '"><img class="icon" src="view/icon/editer.png">Edit</a></span> |
            <span><a href="index.php?op=304&id=' . $id . '"><img class="icon" src="view/icon/supprimer.png">Delete</a></span>
            <div><a href="index.php?op=300&id=' . $id . '"><img class="icon" src="view/icon/listEmployee.png">Tous les employeées</a></div>';
        }
    }

    /**
     * Formulaire d'ajout ou de modification d'un enregistrement
     */

    public static function form($msg = '', $preData = [])
    {
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $action = "Modifier"; #Si c'est l'Id qu'on récupère alors l'action sera : Modifier un employé
            $DB = new db_pdo();
            $employee = $DB->querySelect("SELECT * FROM employees WHERE id = '$id'");
            foreach ($employee as $value) {
                $preData['id'] = $value['id'];
                $preData['firstName'] = $value['firstName'];
                $preData['lastName'] = $value['lastName'];
                $preData['extension'] = $value['extension'];
                $preData['email'] = $value['email'];
                if (isset($value['reportsTo'])) {
                    $preData['reportsTo'] = $value['reportsTo'];
                } else {
                    $preData['reportsTo'] = '';
                }
                $preData['jobTitle'] = $value['jobTitle'];
                $preData['officeId'] = $value['officeId'];
            }
        } elseif ($preData == []) {
            $action = "Ajouter"; #Si la table est vide alors l'action sera : Ajouter un employee
            $DB = new db_pdo();
            $result = $DB->querySelect("SELECT MAX(id) as 'max' FROM employees");
            $new_id = $result[0]['max'] + 12;
            $preData = [
                'id' => $new_id,
                'firstName' => '',
                'lastName' => '',
                'extension' => '',
                'email' => '',
                'reportsTo' => '',
                'jobTitle' => '',
                'officeId' => '1'
            ];
        }
        $variable = $msg . '<form action="index.php?op=303" method="POST">
                <h3>' . $action . ' un Employée</h3>
                <div>
                     <label for="id">Numéro</label> 
                    <input type="number" name="id" id="id" maxlength="11" placeholder="numéro employee" value="' . $preData['id'] . '" pattern="[0-9]{4}" size="30" required />
                </div>
                <div>
                    <label for="firstName">firstName</label> 
                    <input type="text" name="firstName" id="firstName" maxlength="50" placeholder="prénom" value="' . $preData['firstName'] . '" size="30" required />
                </div>
                <div>
                    <label for="lastName">lastName</label> 
                    <input type="text" name="lastName" id="lastName" maxlength="50" placeholder="nom" value="' . $preData['lastName'] . '" size="30" required />
                </div>
                <div>
                     <label for="extension">Extension</label> 
                    <input type="text" name="extension" id="extension" maxlength="10" placeholder="extension" value="' . $preData['extension'] . '" size="30" required />
                </div>
                <div>
                     <label for="email">Email</label> 
                    <input type="email" name="email" id="email" maxlength="100" placeholder="courriel" value="' . $preData['email'] . '" size="30" required />
                </div>
                <div>
                <label for="officeId">officeId</label>
                    <select name="officeId" maxlength="10" id="officeId" required>';

        #Récupérer tous les identifiants des bureaux
        $DB = new db_pdo();
        $offices = $DB->querySelect("SELECT id FROM offices");
        //var_dump($offices);
        foreach ($offices as $officeId) {
            if ($preData['officeId'] == $officeId['id']) {
                $variable .= '<option value="' . $officeId['id'] . '" selected>' . $officeId['id'] . '</option>';
            } else {
                $variable .= '<option value="' . $officeId['id'] . '">' . $officeId['id'] . '</option>';
            }
        }

        $variable .= '</select>
                </div>
                <div>
                     <label for="reportsTo">Numéro de rapport</label> 
                    <input type="number" name="reportsTo" id="reportsTo" maxlength="11" placeholder="numéro de raport de vente" min="0" step="1" value="' . $preData['reportsTo'] . '" size="30" />
                </div>
                <div>
                    <label for="jobTitle">jobTitle</label> 
                    <input type="text" name="jobTitle" id="jobTitle" maxlength="50" placeholder="titre d\'emplo" value="' . $preData['jobTitle'] . '" size="30" required />
                </div>
                <div>
                    <input type="submit" value="Continuez" />
                    <button type="button" onclick="history.back();">Annuler</button>
                </div>
            </form>
        ';
        return $variable;
    }

    public static function save()
    {
        $msg = '';

        #Vérifier l'identifiant
        if (!isset($_POST['id'])) {
            crash(400, "numéro employee manquant");
        } else {
            $id = $_POST['id'];
            $id = htmlspecialchars($id);
            if (strlen($id) == 0 || strlen($id) > 11) {
                crash(400, "erreur taille prénom max 11");
            }

            #Véririfier si l'identifiant existe dans la table 
            $DB = new db_pdo();
            $sql = "SELECT * FROM employees WHERE id = ?";
            $employees = $DB->querySelectParam($sql, [$id]);
            if (count($employees) == 1) {
                $msg = 'Ce numéro d\'emplyee existe déjà';
            }
        }

        #Vérifier le prénom
        if (!isset($_POST['firstName'])) {
            crash(400, "prénom manquant");
        } else {
            $firstName = $_POST['firstName'];
            $firstName = htmlspecialchars($firstName);
            if (strlen($firstName) == 0 || strlen($firstName) > 50) {
                crash(400, "erreur taille prénom max 50");
            }
        }

        #Vérifier le nom
        if (!isset($_POST['lastName'])) {
            crash(400, "nom manquant");
        } else {
            $lastName = $_POST['lastName'];
            $lastName = htmlspecialchars($lastName);
            if (strlen($lastName) == 0 || strlen($lastName) > 50) {
                crash(400, "erreur taille non complet max 50");
            }
        }

        #Vérifier l'extension
        if (!isset($_POST['extension'])) {
            crash(400, "nom manquant");
        } else {
            $extension = $_POST['extension'];
            $extension = htmlspecialchars($extension);
            if (strlen($extension) == 0 || strlen($extension) > 10) {
                crash(400, "erreur taille extension max 10");
            }
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
                $sql = "SELECT * FROM employees WHERE email = ?";
                $employees = $DB->querySelectParam($sql, [$email]);
                if (count($employees) == 1) {
                    $msg = 'Ce email est déjà utilisé, svp. choisir un autre email ou login';
                }
            }
        }

        #Vérifier l'identifiant du bureau
        if (!isset($_POST['officeId'])) {
            crash(400, "identifiant du bureau manquant");
        } else {
            $officeId = $_POST['officeId'];
            $officeId = htmlspecialchars($officeId);
        }

        #Vérifier le numéro de rapport
        if (!isset($_POST['reportsTo'])) {
            $reportsTo = null;
        } else {
            $reportsTo = $_POST['reportsTo'];
        }

        #Vérifier le titre d'emploi
        if (!isset($_POST['jobTitle'])) {
            crash(400, "titre d'emploi manquant");
        } else {
            $jobTitle = $_POST['jobTitle'];
            $jobTitle = htmlspecialchars($jobTitle);
            if (strlen($jobTitle) == 0 || strlen($jobTitle) > 50) {
                crash(400, "erreur taille titre d'emploi max 50");
            }
        }

        if ($msg != '') {
            return self::form($msg, $_REQUEST);
        } else {
            $_SESSION['email'] = $email;

            $DB = new db_pdo();

            $params = [$id, $lastName, $firstName, $extension, $email, $officeId, $reportsTo, $jobTitle];

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'ajouter') {
                $sql = "INSERT INTO employees (id,  lastName, firstName, extension, email, officeId , reportsTo , jobTitle)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";
                $result = $DB->queryParam($sql, $params);

                return "Employee Ajouté avec succée";
            } elseif (isset($_REQUEST['id'])) {
                $id = $_REQUEST['id'];
                $sql = "UPDATE employees SET id = ?, lastName = ?, firstName = ?, extension = ?, email = ?, officeId = ?, reportsTo = ?, jobTitle = ? WHERE id = ?";
                $result = $DB->queryParam($sql, $params);

                return "Employee Modifié avec succée";
            }
        }
    }

    /**
     * Supprime un employé
     */
    public static function delete()
    {
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];

            $DB = new db_pdo();
            $result = $DB->queryParam("DELETE FROM employees WHERE id = ?", [$id]);
            return "<div>Employee supprimer avec succée</div>" . self::list();
        }
    }

    /**
     * La liste de tous les employées en format JSON
     */
    public static function listJSON()
    {
        $DB = new db_pdo();
        $employees = $DB->table("employees");
        $employeesJSON = json_encode($employees, JSON_PRETTY_PRINT);
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(200);
        echo $employeesJSON;
    }
}
