<?php
if (!isset($index_loaded)) {
    header('HTTP/1.0 403 Cette page n\'est pa accessible directement');
    exit('Erreur : Cette page n\'est pas accessible directement');
}
?>

<footer>
    Exercice par <?= AUTHOR; ?> &copy;
    <div>
        <?= COMPANY_STREET_ADDRESS ?>
        <?= COMPANY_CITY ?>
        <?= COMPANY_PROVINCE ?>
        <?= COMPANY_COUNTRY ?>
        <?= COMPANY_POSTAL_CODE ?>
    </div>

    <div>
        <?= COMPANY_PHONE ?>
        <a href="mailto:<?= COMPANY_EMAIL ?>"><?= COMPANY_EMAIL ?></a>
    </div>
    <div>
        <?php
        if (isset($pageData['view_count'])) {
            echo 'nombre de vue : ' . $pageData['view_count'];
        }
        ?>
    </div>
</footer>
</div>
</body>

</html>