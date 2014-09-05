<?php
define("DEBUG", true);                  // Pour débuger à la mimine
define("BASE_PATH", str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])));

ini_set('display_errors', 1);           // Afficher les erreurs (au cas ou désactivé)
ini_set('display_startup_errors',1);
error_reporting(DEBUG ? -1 : E_ERROR);  // N'afficher que les erreurs (pas les warning) en production (tout en debug)

setlocale(LC_TIME, 'fr_FR.UTF8');       // Date et heure en Français avec les fonctions PHP (nécessite locale fr_FR.UTF8 installé sur le serveur)

session_start();                        // On démarre une session (c'est toujours utile :))

define("MYSQL_HOST", "localhost");      // Hôte MySQL
define("MYSQL_DB", "cesi_alternance");  // Base de donnée
define("MYSQL_USER", "root");           // Utilisateur MySQL
define("MYSQL_PASS", "lapsikopass");    // Mot de passe
define("AUTH_HEADER", "X-APP-TOKEN");   // Header Token Name

function autoload($class) {             // Permet l'inclusion automatique des classes (voir namespace PHP) (PHP 5 >= 5.1.2)
    require_once 'class/' . str_replace('\\', '/', $class).'.class.php';
}
spl_autoload_register('autoload');

function exist($class) {                // Vérifier si une class existe (voir namespace PHP)
    return file_exists('class/' . str_replace('\\', '/', $class).'.class.php');
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}