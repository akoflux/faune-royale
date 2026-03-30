<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est connecté
require_role('Client');

$user_id = get_user_id();

// Vérifier que la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profil.php');
    exit();
}

// Récupérer les données
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($current_password)) {
    $errors[] = "Le mot de passe actuel est requis.";
}

if (empty($new_password) || strlen($new_password) < 8) {
    $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
}

if ($new_password !== $confirm_password) {
    $errors[] = "Les mots de passe ne correspondent pas.";
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode(' ', $errors);
    header('Location: profil.php');
    exit();
}

// Vérifier le mot de passe actuel
$stmt = $conn->prepare("SELECT mdp FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($current_password, $user['mdp'])) {
    $_SESSION['error_message'] = "Le mot de passe actuel est incorrect.";
    header('Location: profil.php');
    exit();
}

// Hasher le nouveau mot de passe
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Mettre à jour le mot de passe
$stmt = $conn->prepare("UPDATE users SET mdp = ? WHERE id = ?");
$stmt->bind_param("si", $new_password_hash, $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Votre mot de passe a été modifié avec succès !";
} else {
    $_SESSION['error_message'] = "Une erreur est survenue lors de la modification. Veuillez réessayer.";
}

$stmt->close();
mysqli_close($conn);

header('Location: profil.php');
exit();
?>
