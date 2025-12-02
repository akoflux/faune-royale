<?php
/**
 * Gestion du rate limiting pour protéger contre les attaques par force brute
 */

/**
 * Vérifie si l'utilisateur a dépassé le nombre de tentatives autorisées
 * @param string $identifier Identifiant unique (email, IP, etc.)
 * @param int $max_attempts Nombre maximum de tentatives
 * @param int $time_window Fenêtre de temps en secondes (par défaut 15 minutes)
 * @return bool True si l'utilisateur peut continuer, False si limité
 */
function check_rate_limit($identifier, $max_attempts = 5, $time_window = 900) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $key = 'rate_limit_' . md5($identifier);
    $current_time = time();

    // Initialiser ou récupérer les données de rate limiting
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => $current_time,
            'locked_until' => 0
        ];
    }

    $rate_data = $_SESSION[$key];

    // Vérifier si l'utilisateur est verrouillé
    if ($rate_data['locked_until'] > $current_time) {
        $remaining_time = $rate_data['locked_until'] - $current_time;
        $minutes = ceil($remaining_time / 60);
        return [
            'allowed' => false,
            'message' => "Trop de tentatives. Veuillez réessayer dans $minutes minute(s).",
            'remaining_time' => $remaining_time
        ];
    }

    // Réinitialiser si la fenêtre de temps est expirée
    if ($current_time - $rate_data['first_attempt'] > $time_window) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => $current_time,
            'locked_until' => 0
        ];
        $rate_data = $_SESSION[$key];
    }

    // Incrémenter le compteur de tentatives
    $_SESSION[$key]['attempts']++;

    // Vérifier si la limite est dépassée
    if ($_SESSION[$key]['attempts'] > $max_attempts) {
        $_SESSION[$key]['locked_until'] = $current_time + $time_window;
        $minutes = ceil($time_window / 60);
        return [
            'allowed' => false,
            'message' => "Trop de tentatives échouées. Votre compte est temporairement verrouillé pour $minutes minutes.",
            'remaining_time' => $time_window
        ];
    }

    $remaining_attempts = $max_attempts - $_SESSION[$key]['attempts'] + 1;

    return [
        'allowed' => true,
        'attempts' => $_SESSION[$key]['attempts'],
        'remaining_attempts' => $remaining_attempts
    ];
}

/**
 * Réinitialise le compteur de tentatives pour un identifiant
 * @param string $identifier Identifiant unique
 */
function reset_rate_limit($identifier) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $key = 'rate_limit_' . md5($identifier);
    unset($_SESSION[$key]);
}

/**
 * Enregistre une tentative de connexion dans la base de données (optionnel)
 * @param mysqli $conn Connexion à la base de données
 * @param string $email Email de l'utilisateur
 * @param bool $success Succès ou échec de la tentative
 */
function log_login_attempt($conn, $email, $success) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $status = $success ? 'success' : 'failed';

    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, user_agent, status, created_at) VALUES (?, ?, ?, ?, NOW())");

    if ($stmt) {
        $stmt->bind_param("ssss", $email, $ip, $user_agent, $status);
        $stmt->execute();
        $stmt->close();
    }
}
?>
