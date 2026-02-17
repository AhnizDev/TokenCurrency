<?php
// On initialise la session pour pouvoir la manipuler
session_start();

// On détruit toutes les variables de session (nettoyage)
session_unset();

// On détruit la session elle-même au niveau du serveur
session_destroy();

// Redirection immédiate vers la page de connexion
header("Location: auth.php");
exit();
?>
