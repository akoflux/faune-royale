<?php
/**
 * Fichier de gestion de l'authentification et des sessions
 * Inclure ce fichier en haut de chaque page protégée
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function est_connecte() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['id']);
}

/**
 * Obtient le rôle de l'utilisateur connecté
 * @return string|null
 */
function get_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Obtient l'ID de l'utilisateur connecté
 * @return int|null
 */
function get_user_id() {
    return $_SESSION['id'] ?? null;
}

/**
 * Obtient le prénom de l'utilisateur connecté
 * @return string|null
 */
function get_prenom() {
    return $_SESSION['prenom'] ?? null;
}

/**
 * Obtient l'email de l'utilisateur connecté
 * @return string|null
 */
function get_email() {
    return $_SESSION['email'] ?? null;
}

/**
 * Redirige vers la page de connexion si l'utilisateur n'est pas connecté
 * @param string $message Message d'erreur optionnel
 */
function require_connexion($message = "Vous devez être connecté pour accéder à cette page.") {
    if (!est_connecte()) {
        $_SESSION['erreur_auth'] = $message;
        header('Location: /ProjetZoo/Connexion/connexion.html');
        exit();
    }
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * @param string|array $roles_autorises Un rôle ou un tableau de rôles autorisés
 * @return bool
 */
function a_role($roles_autorises) {
    if (!est_connecte()) {
        return false;
    }

    $role_utilisateur = get_role();

    if (is_array($roles_autorises)) {
        return in_array($role_utilisateur, $roles_autorises);
    }

    return $role_utilisateur === $roles_autorises;
}

/**
 * Redirige vers la page d'erreur si l'utilisateur n'a pas le bon rôle
 * @param string|array $roles_autorises Un rôle ou un tableau de rôles autorisés
 * @param string $message Message d'erreur personnalisé
 */
function require_role($roles_autorises, $message = "Vous n'avez pas les permissions nécessaires.") {
    require_connexion();

    if (!a_role($roles_autorises)) {
        $_SESSION['erreur_auth'] = $message;
        // Redirection vers la page appropriée selon le rôle
        rediriger_selon_role();
        exit();
    }
}

/**
 * Redirige l'utilisateur vers sa page d'accueil selon son rôle
 */
function rediriger_selon_role() {
    if (!est_connecte()) {
        header('Location: /ProjetZoo/Connexion/connexion.html');
        exit();
    }

    $role = get_role();

    switch ($role) {
        case 'Directeur':
            header('Location: /ProjetZoo/Direction/main.php');
            break;
        case 'Chef_Equipe':
            header('Location: /ProjetZoo/Direction/main.php');
            break;
        case 'Veterinaire':
            header('Location: /ProjetZoo/Veterinaire/main.php');
            break;
        case 'Employe':
            header('Location: /ProjetZoo/Employes/main.php');
            break;
        case 'Benevole':
            header('Location: /ProjetZoo/Benevole/main.php');
            break;
        case 'Client':
            header('Location: /ProjetZoo/Client/main.php');
            break;
        default:
            session_destroy();
            header('Location: /ProjetZoo/Connexion/connexion.html');
            break;
    }
    exit();
}

/**
 * Déconnecte l'utilisateur
 */
function deconnecter() {
    session_unset();
    session_destroy();
    header('Location: /ProjetZoo/index.html');
    exit();
}

/**
 * Enregistre une activité dans les logs
 * @param string $action L'action effectuée
 * @param string $module Le module concerné
 * @param string $details Détails supplémentaires
 */
function log_activite($action, $module = '', $details = '') {
    if (!est_connecte()) {
        return;
    }

    global $conn;

    // Si $conn n'est pas disponible, on charge la connexion
    if (!isset($conn)) {
        require_once __DIR__ . '/../connexion.php';
    }

    // Vérifier que la connexion est bien établie
    if (!isset($conn) || !$conn) {
        return; // Échec silencieux si pas de connexion
    }

    $user_id = get_user_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $conn->prepare("INSERT INTO logs_activite (user_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $action, $module, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Vérifie si la session est encore valide (timeout après 2 heures d'inactivité)
 */
function verifier_timeout_session() {
    $timeout = 7200; // 2 heures en secondes

    if (isset($_SESSION['derniere_activite']) && (time() - $_SESSION['derniere_activite'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: /ProjetZoo/Connexion/connexion.html?timeout=1');
        exit();
    }

    $_SESSION['derniere_activite'] = time();
}

// Vérifier le timeout automatiquement à chaque inclusion du fichier
if (est_connecte()) {
    verifier_timeout_session();
}
