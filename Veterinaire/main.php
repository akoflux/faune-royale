<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est vétérinaire
require_role('Veterinaire');

$prenom = get_prenom();

// Rediriger vers le dashboard Employes qui gère tous les rôles (Employé, Vétérinaire, Bénévole)
header('Location: ../Employes/main.php');
exit();
?>
