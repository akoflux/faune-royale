<?php
// SÉCURITÉ : Authentification requise
session_start();
require_once("../../../includes/auth.php");
require_once("../../../includes/roles.php");
require_once("../../../connexion.php");

// Vérifier que l'utilisateur a les droits (Vétérinaire, Employé ou Chef d'équipe)
require_role(['Veterinaire', 'Employe', 'Chef_Equipe'], "Vous n'avez pas les permissions pour ajouter des animaux.");

// Vérifier que c'est bien une requête POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ajouter.php");
    exit();
}

// SÉCURITÉ : Récupération et validation des données
$nom = trim($_POST["Nom"] ?? '');
$espece = trim($_POST["espece"] ?? '');
$date_naissance = trim($_POST["date_naissance"] ?? '');
$sexe = trim($_POST["Sexe"] ?? '');
$enclos = trim($_POST["Enclos"] ?? '');

// Validation des champs vides
if (empty($nom) || empty($espece) || empty($date_naissance) || empty($sexe)) {
    echo "<script>alert('Tous les champs obligatoires doivent être remplis'); window.location.href='ajouter.php';</script>";
    exit();
}

// Validation du sexe (doit être Mâle ou Femelle)
if (!in_array($sexe, ['Mâle', 'Femelle'])) {
    echo "<script>alert('Sexe invalide'); window.location.href='ajouter.php';</script>";
    exit();
}

// Validation de la date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_naissance)) {
    echo "<script>alert('Format de date invalide'); window.location.href='ajouter.php';</script>";
    exit();
}

// SÉCURITÉ : Utilisation de requête préparée pour éviter l'injection SQL
$stmt = mysqli_prepare($conn, "INSERT INTO animaux (Nom, Espece, date_naissance, Sexe, Enclos) VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    echo "<script>alert('Erreur lors de la préparation de la requête'); window.location.href='ajouter.php';</script>";
    mysqli_close($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, "sssss", $nom, $espece, $date_naissance, $sexe, $enclos);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Log de l'activité AVANT de fermer la connexion
    log_activite("Ajout animal", "Animaux", "Animal '$nom' ajouté");

    mysqli_close($conn);

    echo "<script>alert('Animal ajouté avec succès !'); window.location.href='ajouter.php';</script>";
    exit();
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo "<script>alert('Erreur lors de l\\'ajout de l\\'animal. Veuillez réessayer.'); window.location.href='ajouter.php';</script>";
    exit();
}
?>
