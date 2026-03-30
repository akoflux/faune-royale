<?php
// SÉCURITÉ : Authentification requise
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est un client
require_role('Client', "Accès réservé aux clients.");

$user_id = get_user_id();

// Vérifier que l'ID de réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID de réservation manquant'); window.location.href='reservations.php';</script>";
    exit();
}

// SÉCURITÉ : Validation de l'ID
$reservation_id = intval($_GET['id']);

if ($reservation_id <= 0) {
    echo "<script>alert('ID de réservation invalide'); window.location.href='reservations.php';</script>";
    exit();
}

// Vérifier que la réservation appartient bien au client connecté
$check_stmt = mysqli_prepare($conn, "SELECT id, statut, date_visite FROM reservations WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($check_stmt, "ii", $reservation_id, $user_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$reservation = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_stmt);

if (!$reservation) {
    echo "<script>alert('Réservation introuvable ou vous n\\'avez pas accès à cette réservation'); window.location.href='reservations.php';</script>";
    exit();
}

// Vérifier que la réservation n'est pas déjà annulée
if ($reservation['statut'] === 'annulee') {
    echo "<script>alert('Cette réservation est déjà annulée'); window.location.href='reservations.php';</script>";
    exit();
}

// Vérifier que la date de visite n'est pas dépassée
$date_visite = new DateTime($reservation['date_visite']);
$date_actuelle = new DateTime();

if ($date_visite < $date_actuelle) {
    echo "<script>alert('Impossible d\\'annuler une réservation passée'); window.location.href='reservations.php';</script>";
    exit();
}

// SÉCURITÉ : Utilisation de requête préparée pour l'annulation
$stmt = mysqli_prepare($conn, "UPDATE reservations SET statut = 'annulee' WHERE id = ? AND user_id = ?");

if (!$stmt) {
    echo "<script>alert('Erreur lors de la préparation de la requête'); window.location.href='reservations.php';</script>";
    mysqli_close($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $reservation_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Log de l'activité AVANT de fermer la connexion
    log_activite("Annulation réservation", "Réservations", "Réservation #$reservation_id annulée par le client");

    mysqli_close($conn);

    echo "<script>alert('Réservation annulée avec succès !'); window.location.href='reservations.php';</script>";
    exit();
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo "<script>alert('Erreur lors de l\\'annulation de la réservation. Veuillez réessayer.'); window.location.href='reservations.php';</script>";
    exit();
}
?>
