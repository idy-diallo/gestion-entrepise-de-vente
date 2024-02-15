<?php
if (!isset($index_loaded)) {
    header('HTTP/1.0 403 Cette page n\'est pa accessible directement');
    exit('Erreur : Cette page n\'est pas accessible directement');
}
?>

<header>
    <h2>
        <?= $pageData['title']; ?>
    </h2>
</header>