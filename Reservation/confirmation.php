<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

require_connexion();

$reservation_id = $_GET['id'] ?? null;

if (!$reservation_id) {
    header('Location: reservation.php');
    exit();
}

// Récupérer les détails de la réservation
$user_id = get_user_id();
$stmt = $conn->prepare("
    SELECT * FROM reservations
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['erreur_reservation'] = "Réservation non trouvée";
    header('Location: reservation.php');
    exit();
}

$reservation = $result->fetch_assoc();
$stmt->close();

// Formater les données
$forfait_labels = [
    'demi_journee' => 'Demi-journée (13h-18h)',
    '1_jour' => '1 Jour (9h-18h)',
    '2_jours_1_nuit' => '2 Jours + 1 Nuit'
];

$statut_labels = [
    'en_attente' => 'En attente',
    'confirmee' => 'Confirmée',
    'annulee' => 'Annulée'
];

$date_obj = new DateTime($reservation['date_visite']);
$date_formatee = $date_obj->format('l d F Y');
setlocale(LC_TIME, 'fr_FR.UTF-8');
$date_formatee = strftime('%A %d %B %Y', $date_obj->getTimestamp());
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation - Zoo Paradis</title>
    <link rel="stylesheet" href="reservation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .success-icon {
            text-align: center;
            margin: 2rem 0;
        }

        .success-icon i {
            font-size: 5rem;
            color: var(--success);
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .confirmation-card {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .confirmation-card h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--success), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .confirmation-number {
            font-size: 1.5rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .confirmation-number strong {
            color: var(--primary);
        }

        .reservation-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-value {
            color: var(--text);
            font-weight: 600;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1.5rem 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .total-row .detail-value {
            color: var(--success);
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .info-message {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--primary);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }

        .info-message h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .info-message ul {
            list-style: none;
            padding-left: 0;
        }

        .info-message li {
            padding: 0.5rem 0;
            display: flex;
            align-items: start;
            gap: 0.5rem;
        }

        .info-message li i {
            color: var(--success);
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1>Réservation confirmée !</h1>
            <p class="confirmation-number">
                Numéro de réservation : <strong>#<?php echo $reservation['id']; ?></strong>
            </p>

            <div class="reservation-details">
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-user"></i> Nom complet
                    </span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-envelope"></i> Email
                    </span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($reservation['email']); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-phone"></i> Téléphone
                    </span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($reservation['telephone']); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar"></i> Date de visite
                    </span>
                    <span class="detail-value">
                        <?php echo ucfirst($date_formatee); ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-star"></i> Forfait
                    </span>
                    <span class="detail-value">
                        <?php echo $forfait_labels[$reservation['forfait']]; ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-users"></i> Visiteurs
                    </span>
                    <span class="detail-value">
                        <?php echo $reservation['nombre_adultes']; ?> adulte(s),
                        <?php echo $reservation['nombre_enfants']; ?> enfant(s)
                    </span>
                </div>

                <?php if (!empty($reservation['commentaire'])): ?>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-comment"></i> Commentaire
                    </span>
                    <span class="detail-value">
                        <?php echo nl2br(htmlspecialchars($reservation['commentaire'])); ?>
                    </span>
                </div>
                <?php endif; ?>

                <div class="total-row">
                    <span class="detail-label">Prix total</span>
                    <span class="detail-value">
                        <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €
                    </span>
                </div>
            </div>

            <div class="info-message">
                <h3><i class="fas fa-info-circle"></i> Prochaines étapes</h3>
                <ul>
                    <li>
                        <i class="fas fa-check"></i>
                        Un email de confirmation a été envoyé à votre adresse
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        Présentez votre numéro de réservation à l'entrée du zoo
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        Vous pouvez annuler gratuitement jusqu'à 48h avant la date
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        Retrouvez vos réservations dans votre espace client
                    </li>
                </ul>
            </div>

            <div class="actions">
                <a href="/ProjetZoo/Client/main.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Mon espace
                </a>
                <a href="/ProjetZoo/index.html" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>
