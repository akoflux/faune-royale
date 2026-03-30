<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est connecté
require_role('Client');

$user_id = get_user_id();
$user_email = get_email();

// Désactiver l'autocommit pour gérer la transaction
mysqli_autocommit($conn, false);

try {
    // Supprimer toutes les réservations de l'utilisateur
    $stmt = $conn->prepare("DELETE FROM reservations WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Supprimer le compte utilisateur
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Valider la transaction
    mysqli_commit($conn);

    // Détruire la session
    session_unset();
    session_destroy();

    // Rediriger vers la page d'accueil avec message
    mysqli_close($conn);
    header('Location: ../index.html?message=account_deleted');
    exit();

} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    mysqli_rollback($conn);

    $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression de votre compte. Veuillez contacter le support.";
    mysqli_close($conn);

    header('Location: profil.php');
    exit();
}
?>
