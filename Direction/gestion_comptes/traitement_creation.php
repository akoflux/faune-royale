<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../connexion.php';

// Seuls Directeur et Chef d'équipe peuvent accéder
require_role(['Directeur', 'Chef_Equipe']);

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: creer_compte.php');
    exit();
}

// Récupérer les données du formulaire
$role = trim($_POST['role'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$user_id = get_user_id();
$role_createur = get_role();

// Validation
$erreurs = [];

if (empty($role)) $erreurs[] = "Le rôle est requis";
if (empty($nom)) $erreurs[] = "Le nom est requis";
if (empty($prenom)) $erreurs[] = "Le prénom est requis";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide";
if (empty($telephone) || !preg_match('/^[0-9]{10}$/', $telephone)) $erreurs[] = "Téléphone invalide";
if (strlen($password) < 8) $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères";
if ($password !== $confirm_password) $erreurs[] = "Les mots de passe ne correspondent pas";

// Vérifier que l'utilisateur peut créer ce rôle
if (!peut_creer_compte($role)) {
    $erreurs[] = "Vous n'avez pas la permission de créer ce type de compte";
}

if (!empty($erreurs)) {
    $_SESSION['erreur'] = implode('<br>', $erreurs);
    header('Location: creer_compte.php');
    exit();
}

// Vérifier si l'email existe déjà
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['erreur'] = "Cet email est déjà utilisé";
    header('Location: creer_compte.php');
    exit();
}
$stmt->close();

// Hasher le mot de passe
$mdp_hash = password_hash($password, PASSWORD_DEFAULT);

// Insérer le nouvel utilisateur
$stmt = $conn->prepare("
    INSERT INTO users (role, nom, prenom, email, telephone, mdp, actif, cree_par)
    VALUES (?, ?, ?, ?, ?, ?, 1, ?)
");

$stmt->bind_param("ssssssi", $role, $nom, $prenom, $email, $telephone, $mdp_hash, $user_id);

if ($stmt->execute()) {
    $new_user_id = $stmt->insert_id;
    $stmt->close();

    // Logger l'activité
    if (function_exists('log_activite')) {
        log_activite(
            'Création de compte',
            'utilisateurs',
            "Création du compte {$role} pour {$prenom} {$nom} (ID: {$new_user_id})"
        );
    }

    $_SESSION['succes'] = "Le compte a été créé avec succès !";
    header('Location: ../user/dashboard/dashboard.php');
    exit();
} else {
    $_SESSION['erreur'] = "Erreur lors de la création du compte";
    header('Location: creer_compte.php');
    exit();
}
