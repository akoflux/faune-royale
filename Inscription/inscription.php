<?php
// Démarrer la session au début
session_start();

// Inclusion du fichier de connexion
require_once("../connexion.php");

// Vérifier que la connexion est établie
if (!isset($conn) || !$conn) {
    die("Erreur de connexion à la base de données");
}

// Vérifier que c'est bien une requête POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: inscription.html");
    exit();
}

// Récupération et sécurisation des données
$nom = mysqli_real_escape_string($conn, trim($_POST["nom"] ?? ''));
$prenom = mysqli_real_escape_string($conn, trim($_POST["prenom"] ?? ''));
$mail = mysqli_real_escape_string($conn, trim($_POST["email"] ?? ''));
$telephone = mysqli_real_escape_string($conn, trim($_POST["telephone"] ?? ''));
$mdp = trim($_POST["mdp"] ?? '');
$confirm_mdp = trim($_POST["confirm_mdp"] ?? '');

// Validation des champs vides
if (empty($nom) || empty($prenom) || empty($mail) || empty($telephone) || empty($mdp)) {
    echo "<script>alert('Tous les champs sont obligatoires'); window.location.href='inscription.html';</script>";
    exit();
}

// Validation de l'email
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Email invalide'); window.location.href='inscription.html';</script>";
    exit();
}

// Validation du téléphone
if (!preg_match('/^[0-9]{10}$/', $telephone)) {
    echo "<script>alert('Le téléphone doit contenir exactement 10 chiffres'); window.location.href='inscription.html';</script>";
    exit();
}

// Validation de la longueur du mot de passe
if (strlen($mdp) < 8) {
    echo "<script>alert('Le mot de passe doit contenir au moins 8 caractères'); window.location.href='inscription.html';</script>";
    exit();
}

// Vérification de la correspondance des mots de passe
if ($mdp !== $confirm_mdp) {
    echo "<script>alert('Les mots de passe ne correspondent pas'); window.location.href='inscription.html';</script>";
    exit();
}

// Vérifier si l'email existe déjà
$check_query = "SELECT id FROM users WHERE email = ?";
$stmt_check = mysqli_prepare($conn, $check_query);

if (!$stmt_check) {
    echo "<script>alert('Erreur serveur'); window.location.href='inscription.html';</script>";
    exit();
}

mysqli_stmt_bind_param($stmt_check, "s", $mail);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    echo "<script>alert('Cet email est déjà utilisé'); window.location.href='inscription.html';</script>";
    mysqli_stmt_close($stmt_check);
    mysqli_close($conn);
    exit();
}
mysqli_stmt_close($stmt_check);

// Hashage sécurisé du mot de passe
$mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);

// Le rôle est automatiquement "Client" pour les inscriptions publiques
$role = "Client";

// Requête préparée pour éviter les injections SQL
$stmt = mysqli_prepare($conn, "INSERT INTO users (role, nom, prenom, email, telephone, mdp) VALUES (?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo "<script>alert('Erreur lors de la préparation de la requête'); window.location.href='inscription.html';</script>";
    mysqli_close($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, "ssssss", $role, $nom, $prenom, $mail, $telephone, $mdp_hash);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo "<script>
        alert('Inscription effectuée avec succès ! Vous pouvez maintenant vous connecter.');
        window.location.href='../Connexion/connexion.html';
    </script>";
    exit();
} else {
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo "<script>alert('Erreur lors de l\\'inscription. Veuillez réessayer.'); window.location.href='inscription.html';</script>";
    exit();
}
?>
