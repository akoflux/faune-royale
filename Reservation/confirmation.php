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
    <title>Confirmation de réservation - Faune Royal</title>
    <link rel="stylesheet" href="../global-nature-zoo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #faf8f3 0%, #f4e7d7 50%, #e8d5c4 100%);
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
            padding: 2rem 1rem;
        }

        .confirmation-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .success-icon {
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon i {
            font-size: 5rem;
            color: #4a7c2c;
            animation: scaleIn 0.5s ease;
            filter: drop-shadow(0 4px 10px rgba(74, 124, 44, 0.3));
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .confirmation-card {
            background: white;
            border: 3px solid #8b4513;
            border-radius: 30px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(62, 39, 35, 0.15);
            position: relative;
            overflow: hidden;
        }

        .confirmation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #4a7c2c, #d97218, #daa520);
        }

        .confirmation-card h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2d5016;
            font-weight: 800;
        }

        .confirmation-number {
            font-size: 1.3rem;
            color: #6b5d52;
            margin-bottom: 2.5rem;
        }

        .confirmation-number strong {
            color: #d97218;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .reservation-details {
            background: rgba(244, 231, 215, 0.4);
            border: 2px solid rgba(139, 69, 19, 0.2);
            border-radius: 20px;
            padding: 2.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1.2rem 0;
            border-bottom: 1px solid rgba(139, 69, 19, 0.15);
            align-items: center;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b5d52;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-weight: 500;
        }

        .detail-label i {
            color: #d97218;
            font-size: 1.1rem;
        }

        .detail-value {
            color: #2d5016;
            font-weight: 700;
            text-align: right;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2rem 0 0 0;
            font-size: 1.8rem;
            font-weight: 800;
            border-top: 3px solid #4a7c2c;
            margin-top: 1rem;
        }

        .total-row .detail-label {
            color: #2d5016;
        }

        .total-row .detail-value {
            color: #4a7c2c;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1.2rem 2.5rem;
            border-radius: 15px;
            border: none;
            font-family: 'Nunito', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5016, #4a7c2c);
            color: white;
            box-shadow: 0 4px 15px rgba(74, 124, 44, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #2d5016;
            border: 2px solid #8b4513;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 124, 44, 0.4);
        }

        .btn-secondary:hover {
            background: rgba(244, 231, 215, 0.5);
            border-color: #4a7c2c;
        }

        .info-message {
            background: rgba(74, 124, 44, 0.08);
            border: 2px solid rgba(74, 124, 44, 0.3);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2.5rem;
            text-align: left;
        }

        .info-message h3 {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 1.5rem;
            color: #2d5016;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .info-message h3 i {
            color: #d97218;
            font-size: 1.5rem;
        }

        .info-message ul {
            list-style: none;
            padding-left: 0;
        }

        .info-message li {
            padding: 0.8rem 0;
            display: flex;
            align-items: start;
            gap: 0.8rem;
            color: #6b5d52;
            line-height: 1.6;
        }

        .info-message li i {
            color: #4a7c2c;
            margin-top: 0.3rem;
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .confirmation-card {
                padding: 2rem 1.5rem;
            }

            .confirmation-card h1 {
                font-size: 1.8rem;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .detail-value {
                text-align: left;
            }

            .total-row {
                font-size: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
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
