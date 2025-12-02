<?php
/**
 * Gestion des tokens CSRF (Cross-Site Request Forgery)
 * Protection contre les attaques CSRF
 */

/**
 * Génère un nouveau token CSRF et le stocke en session
 * @return string Le token généré
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Générer un token aléatoire sécurisé
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;

    return $token;
}

/**
 * Récupère le token CSRF actuel ou en génère un nouveau
 * @return string Le token CSRF
 */
function get_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        return generate_csrf_token();
    }

    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le token CSRF fourni est valide
 * @param string $token Le token à vérifier
 * @return bool True si valide, False sinon
 */
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Utiliser hash_equals pour éviter les attaques de timing
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Génère un champ input hidden pour les formulaires
 * @return string Le HTML du champ input
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Vérifie le token CSRF depuis une requête POST et arrête l'exécution si invalide
 * @param string $redirect_url URL de redirection en cas d'échec
 */
function require_csrf($redirect_url = null) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($token)) {
        if ($redirect_url) {
            $_SESSION['csrf_error'] = "Token de sécurité invalide. Veuillez réessayer.";
            header("Location: $redirect_url");
            exit();
        } else {
            die("Token CSRF invalide. Tentative d'attaque détectée.");
        }
    }
}
?>
