<?php
// Démarrer la session au début
session_start();

// Inclusion des fichiers nécessaires
require_once("../connexion.php");
require_once("../includes/rate_limit.php");

// Vérifier que la connexion est établie
if (!isset($conn) || !$conn) {
    die("Erreur de connexion à la base de données");
}

// Vérifier que c'est bien une requête POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: connexion.html");
    exit();
}

// Récupération des données
$email = mysqli_real_escape_string($conn, trim($_POST["email"] ?? ''));
$password = trim($_POST["password"] ?? '');

// Validation des champs vides
if (empty($email) || empty($password)) {
    echo "<script>alert('Tous les champs sont obligatoires'); window.location.href='connexion.html';</script>";
    exit();
}

// SÉCURITÉ : Vérification du rate limiting
$rate_check = check_rate_limit($email, 5, 900); // 5 tentatives max en 15 minutes

if (!$rate_check['allowed']) {
    echo "<script>alert('" . addslashes($rate_check['message']) . "'); window.location.href='connexion.html';</script>";
    exit();
}

// Utilisation de requête préparée pour la sécurité
$stmt = mysqli_prepare($conn, "SELECT id, prenom, email, role, mdp, actif FROM users WHERE email = ?");

if (!$stmt) {
    echo "<script>alert('Erreur serveur'); window.location.href='connexion.html';</script>";
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

    // Vérifier si le compte est actif (si la colonne existe)
    if (isset($user['actif']) && $user['actif'] == 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Log de tentative échouée
        // log_login_attempt($conn, $email, false);

        echo "<script>alert('Votre compte a été désactivé. Contactez l\\'administrateur.'); window.location.href='connexion.html';</script>";
        exit();
    }

    // Vérification du mot de passe avec password_verify
    if (password_verify($password, $user['mdp'])) {

        // SÉCURITÉ : Réinitialiser le rate limit en cas de succès
        reset_rate_limit($email);

        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);

        // Stocker les infos en session
        $_SESSION['id'] = $user['id'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['derniere_activite'] = time();

        // Générer un token CSRF pour les futures requêtes
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Log de connexion réussie
        // log_login_attempt($conn, $email, true);

        // Redirection en fonction du rôle
        switch ($user['role']) {
            case 'Directeur':
                header("Location: ../Direction/main.php");
                exit();

            case 'Chef_Equipe':
                header("Location: ../Direction/main.php");
                exit();

            case 'Veterinaire':
                header("Location: ../Veterinaire/main.php");
                exit();

            case 'Employe':
                header("Location: ../Employes/main.php");
                exit();

            case 'Benevole':
                header("Location: ../Benevole/main.php");
                exit();

            case 'Client':
                header("Location: ../Client/main.php");
                exit();

            default:
                echo "<script>alert('Rôle non reconnu'); window.location.href='connexion.html';</script>";
                exit();
        }

    } else {
        // Mot de passe incorrect
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Log de tentative échouée
        // log_login_attempt($conn, $email, false);

        // Afficher le nombre de tentatives restantes
        if ($rate_check['remaining_attempts'] <= 3) {
            $message = "Email ou mot de passe incorrect. Il vous reste {$rate_check['remaining_attempts']} tentative(s).";
        } else {
            $message = "Email ou mot de passe incorrect.";
        }

        echo "<script>alert('$message'); window.location.href='connexion.html';</script>";
        exit();
    }
} else {
    // Utilisateur non trouvé
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Log de tentative échouée
    // log_login_attempt($conn, $email, false);

    echo "<script>alert('Email ou mot de passe incorrect.'); window.location.href='connexion.html';</script>";
    exit();
}
?>
