<?php

//Enregistre une erreur fatale dans log du serveur, retourne HTTP response code et message au client
function crash($code, $msg)
{
    // TODO exercice enregistrer l'erreur dans un fichier log/erreurs.log

    //envoyer HTTP response code et message au client
    header('HTTP/1.0 ' . $code . ' ' . $msg);
    exit($msg); // affiche message à l’écran du client et termine le programme
}

function tableHTML($table)
{
    if ($table === []) {
        return 'tableau vide';
    } else {
        //var_dump($table);
        $tabHtml = "<table border = 1>";
        $keys = array_keys($table[0]);
        $tabHtml .= '<tr>';

        foreach ($keys as $key) {
            $tabHtml .= '<th>' . $key . '</th>';
        }
        $tabHtml .= '</tr>';

        foreach ($table as $lignes) {
            $tabHtml .=  '<tr>';
            foreach ($lignes as $ligne) {
                $tabHtml .= '<td>' . $ligne . '</td>';
            }
        }

        $tabHtml .= "</table>";

        return $tabHtml;
    }
}

/**
 * Retourne la liste d'une table avec les actions RUD(RETRIEVE, UPDATE and DELETE)
 */

function tableHTML_LIST($table, $detail = '#', $edit = '#', $delete = '#')
{
    if ($table === []) {
        return 'tableau vide';
    } else {
        //var_dump($table);
        $tabHtml = "<table>";
        $keys = array_keys($table[0]);
        $tabHtml .= '<thead><tr>';

        foreach ($keys as $key) {
            $tabHtml .= '<th>' . $key . '</th>';
        }

        if ($detail != '#' || $edit != '#' || $delete != '#') {
            $tabHtml .= '<th colspan="3">Actions</th>';
        }

        $tabHtml .= '</tr></thead>';

        foreach ($table as $lignes) {
            $tabHtml .=  '<tr>';
            foreach ($lignes as $ligne) {
                $tabHtml .= '<td>' . $ligne . '</td>';
            }

            if ($detail != '#') {
                $tabHtml .= '<td><a href="index.php?op=' . $detail . '&id=' . $lignes['id'] . '"><img class="icon" src="view/icon/detail.png">Voir<a></td>';
            }

            if ($edit != '#') {
                $tabHtml .= '<td><a href="index.php?op=' . $edit . '&id=' . $lignes['id'] . '"><img class="icon" src="view/icon/editer.png">Modifier<a></td>';
            }

            if ($delete != '#') {
                $tabHtml .= '<td><a href="index.php?op=' . $delete . '&id=' . $lignes['id'] . '"><img class="icon" src="view/icon/supprimer.png">Effacer<a></td>';
            }

            $tabHtml .= '</tr>';
        }

        $tabHtml .= "</table>";

        return $tabHtml;
    }
}

/**
 * Formulaire de recherche par un critére qui prend en paramétre l'opération
 */
function search($op)
{
    return '<form action="index.php?op=' . $op . '" method="POST">
                    <label for="chercher">Chercher par id </label>
                        <!--<input type="number" min="0" step="1" name="id" id="chercher" />-->
                        <input type="text" name="id" id="chercher" />
                        <input type="submit" value="Rechercher" />
                        <a href="index.php?op=' . $op . '">Afficher tout</a>
                    </form>
                ';
}
