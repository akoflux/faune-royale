<?php
/**
 * Configuration sécurisée des sessions PHP
 * À inclure au tout début de chaque script utilisant des sessions
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {

    // Configuration des paramètres de sécurité des cookies de session
    ini_set('session.cookie_httponly', 1);  // Empêche l'accès JavaScript aux cookies de session (protection XSS)
    ini_set('session.cookie_secure', 0);     // HTTPS uniquement (mettre à 1 en production avec HTTPS)
    ini_set('session.cookie_samesite', 'Strict'); // Protection CSRF
    ini_set('session.use_strict_mode', 1);   // Refuse les IDs de session non initialisés
    ini_set('session.use_only_cookies', 1);  // Force l'utilisation des cookies uniquement
    ini_set('session.cookie_lifetime', 0);   // Cookie expire à la fermeture du navigateur

    // Nom de session personnalisé (plus difficile à identifier)
    session_name('FAUNEROYAL_SID');

    // Régénération périodique de l'ID de session
    session_start();

    // Régénérer l'ID toutes les 30 minutes pour plus de sécurité
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    // Vérification de l'empreinte du navigateur (détection de vol de session)
    $current_fingerprint = md5(
        $_SERVER['HTTP_USER_AGENT'] ?? '' .
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '' .
        $_SERVER['REMOTE_ADDR'] ?? ''
    );

    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = $current_fingerprint;
    } elseif ($_SESSION['fingerprint'] !== $current_fingerprint) {
        // Possible vol de session détecté
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['security_alert'] = "Session invalide détectée.";
    }
}

/**
 * Détruit complètement la session de manière sécurisée
 */
function secure_session_destroy() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Supprimer toutes les variables de session
        $_SESSION = array();

        // Détruire le cookie de session
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Détruire la session
        session_destroy();
    }
}
?>
