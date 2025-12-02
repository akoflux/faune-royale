<?php
// SÉCURITÉ : Authentification requise
session_start();
require_once("../../../includes/auth.php");
require_once("../../../includes/roles.php");
require_once("../../../connexion.php");

// Vérifier que l'utilisateur a les droits (Directeur ou Chef d'équipe)
require_role(['Directeur', 'Chef_Equipe'], "Vous n'avez pas les permissions pour ajouter des espèces.");

// Vérifier que c'est bien une requête POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ajouter.php");
    exit();
}

// SÉCURITÉ : Récupération et validation des données
$nom = trim($_POST["Nom"] ?? '');
$nourriture = trim($_POST["nourriture"] ?? '');
$duree_vie = trim($_POST["Vie"] ?? '');
$animal_aquatique = trim($_POST["Eau"] ?? '');
$complementaire = trim($_POST["complementaire"] ?? '');

// Validation des champs vides
if (empty($nom) || empty($nourriture) || empty($duree_vie) || empty($animal_aquatique)) {
    echo "<script>alert('Tous les champs obligatoires doivent être remplis'); window.location.href='ajouter.php';</script>";
    exit();
}

// Validation du type aquatique
$types_valides = ['aquatique', 'amphibies', 'non_aquatique', 'Oui', 'Non'];
if (!in_array($animal_aquatique, $types_valides)) {
    echo "<script>alert('Type aquatique invalide'); window.location.href='ajouter.php';</script>";
    exit();
}

// Convertir les anciens formats pour cohérence avec la base de données
if ($animal_aquatique === 'aquatique' || $animal_aquatique === 'amphibies') {
    $animal_aquatique = 'Oui';
} elseif ($animal_aquatique === 'non_aquatique') {
    $animal_aquatique = 'Non';
}

// Validation de la durée de vie (nombre positif)
if (!is_numeric($duree_vie) || $duree_vie < 0) {
    echo "<script>alert('Durée de vie invalide'); window.location.href='ajouter.php';</script>";
    exit();
}

// Vérifier si l'espèce existe déjà
$check_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM especes WHERE nom_race = ?");
mysqli_stmt_bind_param($check_stmt, "s", $nom);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_stmt);

if ($data['count'] > 0) {
    echo "<script>alert('Cette espèce existe déjà'); window.location.href='ajouter.php';</script>";
    exit();
}

// SÉCURITÉ : Utilisation de requête préparée pour éviter l'injection SQL
$stmt = mysqli_prepare($conn, "INSERT INTO especes (nom_race, type_nourriture, duree_vie, animal_aquatique, complementaire) VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    echo "<script>alert('Erreur lors de la préparation de la requête'); window.location.href='ajouter.php';</script>";
    mysqli_close($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, "sssss", $nom, $nourriture, $duree_vie, $animal_aquatique, $complementaire);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Log de l'activité AVANT de fermer la connexion
    log_activite("Ajout espèce", "Espèces", "Espèce '$nom' ajoutée");

    mysqli_close($conn);

    echo "<script>alert('Espèce ajoutée avec succès !'); window.location.href='ajouter.php';</script>";
    exit();
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo "<script>alert('Erreur lors de l\\'ajout de l\\'espèce. Veuillez réessayer.'); window.location.href='ajouter.php';</script>";
    exit();
}
?>
