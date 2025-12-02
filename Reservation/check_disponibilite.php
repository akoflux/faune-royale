<?php
session_start();
require_once __DIR__ . '/../connexion.php';

header('Content-Type: application/json');

if (!isset($_GET['date'])) {
    echo json_encode(['error' => 'Date non fournie']);
    exit();
}

$date = $_GET['date'];

// Vérifier si la date existe déjà dans la table places_disponibles
$stmt = $conn->prepare("SELECT places_reservees, places_totales FROM places_disponibles WHERE date_visite = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $places_reservees = $row['places_reservees'];
    $places_totales = $row['places_totales'];
} else {
    // Si la date n'existe pas encore, initialiser avec 0 réservations
    $places_reservees = 0;
    $places_totales = 200;
}

$stmt->close();

// Calculer également depuis les réservations confirmées
$stmt = $conn->prepare("
    SELECT SUM(nombre_adultes + nombre_enfants) as total_visiteurs
    FROM reservations
    WHERE date_visite = ? AND statut != 'annulee'
");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$places_reservees_confirmees = $row['total_visiteurs'] ?? 0;
$stmt->close();

// Utiliser le maximum entre les deux sources
$places_reservees = max($places_reservees, $places_reservees_confirmees);
$places_restantes = $places_totales - $places_reservees;

echo json_encode([
    'places_totales' => $places_totales,
    'places_reservees' => $places_reservees,
    'places_restantes' => max(0, $places_restantes)
]);
