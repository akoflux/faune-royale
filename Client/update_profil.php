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

// Récupérer et valider les données
$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');

// Validation
$errors = [];

if (empty($prenom) || strlen($prenom) < 2) {
    $errors[] = "Le prénom doit contenir au moins 2 caractères.";
}

if (empty($nom) || strlen($nom) < 2) {
    $errors[] = "Le nom doit contenir au moins 2 caractères.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'adresse email est invalide.";
}

if (empty($telephone) || !preg_match('/^[0-9]{10}$/', $telephone)) {
    $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";
}

// Si erreurs, rediriger avec message
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(' ', $errors);
    header('Location: profil.php');
    exit();
}

// Vérifier si l'email est déjà utilisé par un autre utilisateur
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error_message'] = "Cette adresse email est déjà utilisée par un autre compte.";
    $stmt->close();
    header('Location: profil.php');
    exit();
}
$stmt->close();

// Mettre à jour les informations
$stmt = $conn->prepare("UPDATE users SET prenom = ?, nom = ?, email = ?, telephone = ? WHERE id = ?");
$stmt->bind_param("ssssi", $prenom, $nom, $email, $telephone, $user_id);

if ($stmt->execute()) {
    // Mettre à jour la session
    $_SESSION['prenom'] = $prenom;
    $_SESSION['email'] = $email;

    $_SESSION['success_message'] = "Vos informations ont été mises à jour avec succès !";
} else {
    $_SESSION['error_message'] = "Une erreur est survenue lors de la mise à jour. Veuillez réessayer.";
}

$stmt->close();
mysqli_close($conn);

header('Location: profil.php');
exit();
?>
