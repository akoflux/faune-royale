<?php
// SÉCURITÉ : Authentification requise
session_start();
require_once("../../../includes/auth.php");
require_once("../../../includes/roles.php");
require_once("../../../connexion.php");

// Vérifier que l'utilisateur a les droits (Vétérinaire ou Employé, PAS Bénévole)
require_role(['Veterinaire', 'Employe'], "Vous n'avez pas les permissions pour supprimer des animaux.");

// Vérifier que c'est bien une requête POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit();
}

// SÉCURITÉ : Vérification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('Token de sécurité invalide. Veuillez réessayer.'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// Vérifier que l'ID est fourni
if (!isset($_POST["id"])) {
    echo "<script>alert('ID manquant'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// SÉCURITÉ : Validation et conversion en entier
$id = intval($_POST["id"]);

if ($id <= 0) {
    echo "<script>alert('ID invalide'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// Récupérer le nom de l'animal avant suppression pour le log
$stmt_get = mysqli_prepare($conn, "SELECT Nom FROM animaux WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$animal = mysqli_fetch_assoc($result);
$nom_animal = $animal['Nom'] ?? 'Inconnu';
mysqli_stmt_close($stmt_get);

// SÉCURITÉ : Utilisation de requête préparée
$stmt = mysqli_prepare($conn, "DELETE FROM animaux WHERE id = ?");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Log de l'activité
        log_activite("Suppression animal", "Animaux", "Animal '$nom_animal' (ID: $id) supprimé");

        mysqli_close($conn);

        echo "<script>alert('Animal supprimé avec succès !'); window.location.href = 'dashboard.php';</script>";
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo "<script>alert('Erreur lors de la suppression.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }
} else {
    mysqli_close($conn);
    echo "<script>alert('Erreur de préparation de la requête'); window.location.href = 'dashboard.php';</script>";
    exit();
}
?>
