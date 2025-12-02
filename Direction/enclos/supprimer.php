<?php
// SÉCURITÉ : Authentification requise
session_start();
require_once("../../includes/auth.php");
require_once("../../includes/roles.php");
require_once("../../connexion.php");

// Vérifier que l'utilisateur a les droits (Directeur ou Chef d'équipe uniquement)
require_role(['Directeur', 'Chef_Equipe'], "Vous n'avez pas les permissions pour supprimer des enclos.");

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

// Vérifier que le nom de l'enclos est fourni
if (!isset($_POST["nom_enclos"]) || empty(trim($_POST["nom_enclos"]))) {
    echo "<script>alert('Nom d\\'enclos manquant'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// SÉCURITÉ : Validation et nettoyage du nom
$nom_enclos = trim($_POST["nom_enclos"]);

// Vérifier que l'enclos existe
$check_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM enclos WHERE Nom = ?");
mysqli_stmt_bind_param($check_stmt, "s", $nom_enclos);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_stmt);

if ($data['count'] == 0) {
    echo "<script>alert('Cet enclos n\\'existe pas'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// SÉCURITÉ : Vérifier qu'aucun animal n'est assigné à cet enclos
$check_animals = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM animaux WHERE Enclos = ?");
mysqli_stmt_bind_param($check_animals, "s", $nom_enclos);
mysqli_stmt_execute($check_animals);
$result_animals = mysqli_stmt_get_result($check_animals);
$animals_data = mysqli_fetch_assoc($result_animals);
mysqli_stmt_close($check_animals);

if ($animals_data['count'] > 0) {
    echo "<script>alert('Impossible de supprimer cet enclos : " . $animals_data['count'] . " animau(x) y sont encore assigné(s). Veuillez d\\'abord déplacer ou supprimer les animaux.'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// SÉCURITÉ : Utilisation de requête préparée pour la suppression
$stmt = mysqli_prepare($conn, "DELETE FROM enclos WHERE Nom = ?");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $nom_enclos);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Log de l'activité AVANT de fermer la connexion
        log_activite("Suppression enclos", "Enclos", "Enclos '$nom_enclos' supprimé");

        mysqli_close($conn);

        echo "<script>alert('Enclos \"$nom_enclos\" supprimé avec succès !'); window.location.href = 'dashboard.php';</script>";
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo "<script>alert('Erreur lors de la suppression de l\\'enclos.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }
} else {
    mysqli_close($conn);
    echo "<script>alert('Erreur de préparation de la requête'); window.location.href = 'dashboard.php';</script>";
    exit();
}
?>
