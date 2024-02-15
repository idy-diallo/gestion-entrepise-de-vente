<?php
if (!isset($index_loaded)) {
    header('HTTP/1.0 403 Cette page n\'est pa accessible directement');
    exit('Erreur : Cette page n\'est pas accessible directement');
}
?>

<nav>
    <a href='index.php'>Acceuil</a> |
    <a href='index.php?op=10'>Contact</a> |
    <a href='index.php?op=50'>Télécharger unPDF</a> |
    <a href='index.php?op=51'>Rediriger</a> |
    <?php
    if (isset($_SESSION['email'])) {
        echo '<a href=\'index.php?op=400\'>Customers</a> |';
        echo '<a href=\'index.php?op=300\'>Employees</a> |';
        echo '<a href=\'index.php?op=20\'>Users</a> |';
        echo '<a href=\'index.php?op=5\'>Logout</a> |';
        echo '<img class="round40" src="user_images/anonyme" alt="icone du user">';
    } else {
        echo '<a href=\'index.php?op=1\'>Login</a> |';
        echo '<a href=\'index.php?op=3\'>Register</a>';
    }
    ?>

    <?php
    if (isset($_SESSION['email'])) {
        echo $_SESSION['email'];
        echo '<img class="round40" src="user_images/' . $_SESSION['user_picture'] . '" alt="icone du user">';
    }
    ?>
</nav>