<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est connecté
require_connexion();

// Vérifier que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reservation.php');
    exit();
}

// Récupérer et valider les données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$date_visite = $_POST['date_visite'] ?? '';
$forfait = $_POST['forfait'] ?? '';
$nb_adultes = intval($_POST['nb_adultes'] ?? 0);
$nb_enfants = intval($_POST['nb_enfants'] ?? 0);
$commentaire = trim($_POST['commentaire'] ?? '');

$user_id = get_user_id();

// Validation des données
$erreurs = [];

if (empty($nom)) $erreurs[] = "Le nom est requis";
if (empty($prenom)) $erreurs[] = "Le prénom est requis";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide";
if (empty($telephone) || !preg_match('/^[0-9]{10}$/', $telephone)) $erreurs[] = "Téléphone invalide (10 chiffres)";
if (empty($date_visite)) $erreurs[] = "La date de visite est requise";
if (!in_array($forfait, ['demi_journee', '1_jour', '2_jours_1_nuit'])) $erreurs[] = "Forfait invalide";
if ($nb_adultes < 0 || $nb_enfants < 0) $erreurs[] = "Nombre de visiteurs invalide";
if ($nb_adultes == 0 && $nb_enfants == 0) $erreurs[] = "Vous devez sélectionner au moins un visiteur";

// Vérifier que la date est dans le futur
$date_obj = new DateTime($date_visite);
$aujourd_hui = new DateTime();
if ($date_obj <= $aujourd_hui) {
    $erreurs[] = "La date de visite doit être dans le futur";
}

if (!empty($erreurs)) {
    $_SESSION['erreur_reservation'] = implode('<br>', $erreurs);
    header('Location: reservation.php');
    exit();
}

// Récupérer les tarifs
$stmt = $conn->prepare("SELECT type, prix FROM tarifs WHERE forfait = ? AND actif = 1");
$stmt->bind_param("s", $forfait);
$stmt->execute();
$result = $stmt->get_result();

$tarifs = [];
while ($row = $result->fetch_assoc()) {
    $tarifs[$row['type']] = $row['prix'];
}
$stmt->close();

if (empty($tarifs)) {
    $_SESSION['erreur_reservation'] = "Tarifs non trouvés pour ce forfait";
    header('Location: reservation.php');
    exit();
}

// Calculer le prix total
$prix_adulte = $tarifs['adulte'] ?? 0;
$prix_enfant = $tarifs['enfant'] ?? 0;
$prix_total = ($nb_adultes * $prix_adulte) + ($nb_enfants * $prix_enfant);

// Vérifier la disponibilité
$total_personnes = $nb_adultes + $nb_enfants;

// Commencer une transaction
$conn->begin_transaction();

try {
    // Vérifier/créer l'entrée dans places_disponibles
    $stmt = $conn->prepare("
        INSERT INTO places_disponibles (date_visite, places_reservees, places_totales)
        VALUES (?, 0, 200)
        ON DUPLICATE KEY UPDATE date_visite = date_visite
    ");
    $stmt->bind_param("s", $date_visite);
    $stmt->execute();
    $stmt->close();

    // Verrouiller la ligne pour éviter les conditions de course
    $stmt = $conn->prepare("
        SELECT places_reservees, places_totales
        FROM places_disponibles
        WHERE date_visite = ?
        FOR UPDATE
    ");
    $stmt->bind_param("s", $date_visite);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $places_reservees = $row['places_reservees'];
    $places_totales = $row['places_totales'];
    $places_restantes = $places_totales - $places_reservees;

    // Vérifier s'il y a assez de places
    if ($places_restantes < $total_personnes) {
        throw new Exception("Plus assez de places disponibles pour cette date. Places restantes: " . $places_restantes);
    }

    // Insérer la réservation
    $stmt = $conn->prepare("
        INSERT INTO reservations
        (user_id, nom, prenom, email, telephone, date_visite, forfait, nombre_adultes, nombre_enfants, prix_total, statut, commentaire)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', ?)
    ");
    $stmt->bind_param(
        "issssssiids",
        $user_id,
        $nom,
        $prenom,
        $email,
        $telephone,
        $date_visite,
        $forfait,
        $nb_adultes,
        $nb_enfants,
        $prix_total,
        $commentaire
    );
    $stmt->execute();
    $reservation_id = $stmt->insert_id;
    $stmt->close();

    // Mettre à jour les places réservées
    $nouveaux_places_reservees = $places_reservees + $total_personnes;
    $stmt = $conn->prepare("
        UPDATE places_disponibles
        SET places_reservees = ?
        WHERE date_visite = ?
    ");
    $stmt->bind_param("is", $nouveaux_places_reservees, $date_visite);
    $stmt->execute();
    $stmt->close();

    // Logger l'activité
    if (function_exists('log_activite')) {
        log_activite('Création de réservation', 'reservations', 'Réservation #' . $reservation_id . ' pour le ' . $date_visite);
    }

    // Valider la transaction
    $conn->commit();

    // Rediriger vers la page de confirmation
    $_SESSION['succes_reservation'] = "Votre réservation a été enregistrée avec succès ! Numéro de réservation: #" . $reservation_id;
    $_SESSION['reservation_id'] = $reservation_id;
    header('Location: confirmation.php?id=' . $reservation_id);
    exit();

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollback();

    $_SESSION['erreur_reservation'] = "Erreur lors de la réservation: " . $e->getMessage();
    header('Location: reservation.php');
    exit();
}
