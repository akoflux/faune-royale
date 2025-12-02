<?php
/**
 * Fichier de connexion à la base de données
 * Utilise maintenant un fichier de configuration centralisé
 */

// Charger la configuration
require_once __DIR__ . '/config.php';

// Créer la connexion sécurisée
$conn = get_db_connection();

// La variable $conn est maintenant disponible pour tous les fichiers qui incluent connexion.php
?>
