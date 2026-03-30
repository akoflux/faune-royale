<?php
/**
 * Configuration de la base de données
 * Fichier de configuration centralisé pour le projet Zoo Paradis
 */

// Configuration de la base de données
define('DB_HOST', 'fauneroyal');
define('DB_USER', 'ako');
define('DB_PASS', 'password123');
define('DB_NAME', 'zoo');
define('DB_CHARSET', 'utf8mb4');

/**
 * Fonction pour obtenir une connexion à la base de données
 * @return mysqli|false Connexion MySQLi ou false en cas d'erreur
 */
function get_db_connection() {
    // Créer la connexion
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Vérifier la connexion
    if ($conn->connect_error) {
        error_log("Erreur de connexion à la base de données: " . $conn->connect_error);
        die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
    }

    // Définir le jeu de caractères
    if (!$conn->set_charset(DB_CHARSET)) {
        error_log("Erreur lors de la définition du charset: " . $conn->error);
    }

    return $conn;
}

// Configuration de sécurité
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');

// Configuration de session (utilisée par includes/auth.php et includes/secure_session.php)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 si HTTPS est utilisé
ini_set('session.cookie_samesite', 'Strict');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
?>
