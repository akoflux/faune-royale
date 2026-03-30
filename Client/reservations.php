<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

require_role('Client');

$user_id = get_user_id();

// Récupérer toutes les réservations du client
$stmt = $conn->prepare("
    SELECT *
    FROM reservations
    WHERE user_id = ?
    ORDER BY date_visite DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();
$stmt->close();

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - Faune Royal</title>
    <link rel="stylesheet" href="../global-nature-zoo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #faf8f3 0%, #f4e7d7 100%);
        }

        .reservation-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .reservation-header {
            background: white;
            border: 3px solid var(--primary-green);
            border-radius: var(--radius-lg);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .reservation-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange), var(--accent-gold));
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            color: var(--primary-green);
            font-size: 1.8rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: white;
            border: 2px solid var(--primary-light);
            border-radius: var(--radius-md);
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .back-button:hover {
            background: var(--primary-light);
            color: white;
        }

        .form-card {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange));
        }

        .form-card h2 {
            color: var(--primary-green);
        }

        .badge-status {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-confirmee {
            background: rgba(74, 124, 44, 0.1);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .status-en_attente {
            background: rgba(217, 114, 24, 0.1);
            color: var(--warning);
            border: 2px solid var(--warning);
        }

        .status-annulee {
            background: rgba(139, 0, 0, 0.1);
            color: var(--danger);
            border: 2px solid var(--danger);
        }

        .info-box {
            background: var(--bg-light);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
        }

        .btn-cancel {
            background: linear-gradient(135deg, #8b0000, #a52a2a);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="reservation-container">
        <header class="reservation-header">
            <div class="header-content">
                <a href="main.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <h1><i class="fas fa-list"></i> Mes réservations</h1>
                <a href="../Connexion/deconnexion.php" class="back-button btn-cancel">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </header>

        <div class="main-content">
            <?php if ($reservations->num_rows > 0): ?>
                <?php while ($resa = $reservations->fetch_assoc()):
                    $date = new DateTime($resa['date_visite']);
                    $is_past = $date < new DateTime();
                ?>
                    <div class="form-card" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <h2 style="margin-bottom: 0.5rem;">Réservation #<?php echo $resa['id']; ?></h2>
                                <span class="badge-status status-<?php echo $resa['statut']; ?>">
                                    <?php echo $statut_labels[$resa['statut']]; ?>
                                </span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-muted);">Créée le</div>
                                <div style="font-size: 0.9rem; color: var(--text-dark);">
                                    <?php echo date('d/m/Y à H:i', strtotime($resa['date_creation'])); ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <div style="color: var(--text-muted); margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-calendar"></i> Date de visite
                                </div>
                                <div style="font-weight: 600; font-size: 1.1rem;">
                                    <?php echo strftime('%A %d %B %Y', $date->getTimestamp()); ?>
                                </div>
                            </div>

                            <div>
                                <div style="color: var(--text-muted); margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-star"></i> Forfait
                                </div>
                                <div style="font-weight: 600;">
                                    <?php echo $forfait_labels[$resa['forfait']]; ?>
                                </div>
                            </div>

                            <div>
                                <div style="color: var(--text-muted); margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-users"></i> Visiteurs
                                </div>
                                <div style="font-weight: 600;">
                                    <?php echo $resa['nombre_adultes']; ?> adulte(s), <?php echo $resa['nombre_enfants']; ?> enfant(s)
                                </div>
                            </div>

                            <div>
                                <div style="color: var(--text-muted); margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-euro-sign"></i> Prix total
                                </div>
                                <div style="font-weight: 700; font-size: 1.3rem; color: var(--success);">
                                    <?php echo number_format($resa['prix_total'], 2, ',', ' '); ?> €
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($resa['commentaire'])): ?>
                            <div class="info-box">
                                <div style="color: var(--text-muted); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-comment"></i> Commentaire
                                </div>
                                <div style="color: var(--text-dark);"><?php echo nl2br(htmlspecialchars($resa['commentaire'])); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$is_past && $resa['statut'] != 'annulee'): ?>
                            <button onclick="annulerReservation(<?php echo $resa['id']; ?>)" class="btn-cancel">
                                <i class="fas fa-times-circle"></i>
                                Annuler cette réservation
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="form-card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--accent-orange); opacity: 0.3; margin-bottom: 1rem;"></i>
                    <h2>Aucune réservation</h2>
                    <p style="color: var(--text-muted); margin: 1rem 0;">Vous n'avez pas encore effectué de réservation</p>
                    <a href="../Reservation/reservation.php" class="btn btn-primary" style="display: inline-flex; margin-top: 1rem;">
                        <i class="fas fa-plus-circle"></i>
                        Réserver maintenant
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function annulerReservation(id) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
                window.location.href = 'annuler_reservation.php?id=' + id;
            }
        }
    </script>
</body>
</html>
