<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est connecté et est un client
require_role('Client', "Accès réservé aux clients.");

$user_id = get_user_id();
$prenom = get_prenom();

// Récupérer les statistiques des réservations du client
$stmt = $conn->prepare("
    SELECT
        COUNT(*) as total_reservations,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN statut = 'confirmee' THEN 1 ELSE 0 END) as confirmees,
        SUM(CASE WHEN date_visite >= CURDATE() AND statut != 'annulee' THEN 1 ELSE 0 END) as a_venir
    FROM reservations
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Récupérer les prochaines réservations
$stmt = $conn->prepare("
    SELECT *
    FROM reservations
    WHERE user_id = ? AND date_visite >= CURDATE() AND statut != 'annulee'
    ORDER BY date_visite ASC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prochaines_reservations = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - Faune Royal</title>
    <link rel="stylesheet" href="../global-nature-zoo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .welcome-section {
            background: white;
            border: 3px solid var(--primary-green);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange), var(--accent-gold));
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-green);
        }

        .welcome-section p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--accent-orange);
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-green), var(--accent-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .stat-card .label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--primary-green), var(--primary-light));
            border: none;
            border-radius: var(--radius-md);
            padding: 1.2rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            box-shadow: var(--shadow-md);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .action-btn.secondary {
            background: white;
            color: var(--primary-green);
            border: 2px solid var(--primary-light);
        }

        .action-btn.secondary:hover {
            background: var(--primary-light);
            color: white;
        }

        .reservations-list {
            background: white;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .reservations-list::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--accent-orange), var(--accent-gold));
        }

        .reservations-list h2 {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--primary-green);
        }

        .reservations-list h2 i {
            color: var(--accent-orange);
        }

        .reservation-item {
            background: var(--bg-light);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .reservation-item:hover {
            background: white;
            border-color: var(--primary-light);
            box-shadow: var(--shadow-sm);
        }

        .reservation-info {
            flex: 1;
        }

        .reservation-date {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }

        .reservation-details {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .reservation-details i {
            color: var(--accent-orange);
        }

        .reservation-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-en_attente {
            background: rgba(217, 114, 24, 0.1);
            color: var(--warning);
            border: 2px solid var(--warning);
        }

        .status-confirmee {
            background: rgba(74, 124, 44, 0.1);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
            color: var(--accent-orange);
        }

        .logout-btn {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #8b0000, #a52a2a);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            z-index: 1000;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-green), var(--primary-light));
            color: white;
            padding: 0.9rem 1.8rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
            box-shadow: var(--shadow-md);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>
    <a href="../Connexion/deconnexion.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Bonjour <?php echo htmlspecialchars($prenom); ?> !</h1>
            <p>Bienvenue dans votre espace client Faune Royal</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <div class="number"><?php echo $stats['total_reservations']; ?></div>
                <div class="label">Réservations totales</div>
            </div>

            <div class="stat-card">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="number"><?php echo $stats['en_attente']; ?></div>
                <div class="label">En attente</div>
            </div>

            <div class="stat-card">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <div class="number"><?php echo $stats['confirmees']; ?></div>
                <div class="label">Confirmées</div>
            </div>

            <div class="stat-card">
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="number"><?php echo $stats['a_venir']; ?></div>
                <div class="label">À venir</div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="../Reservation/reservation.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                Nouvelle réservation
            </a>
            <a href="reservations.php" class="action-btn secondary">
                <i class="fas fa-list"></i>
                Mes réservations
            </a>
            <a href="profil.php" class="action-btn secondary">
                <i class="fas fa-user-circle"></i>
                Mon profil
            </a>
            <a href="../index.html" class="action-btn secondary">
                <i class="fas fa-home"></i>
                Retour à l'accueil
            </a>
        </div>

        <div class="reservations-list">
            <h2><i class="fas fa-calendar-day"></i> Prochaines visites</h2>

            <?php if ($prochaines_reservations->num_rows > 0): ?>
                <?php while ($reservation = $prochaines_reservations->fetch_assoc()):
                    $date = new DateTime($reservation['date_visite']);
                    $forfait_labels = [
                        'demi_journee' => 'Demi-journée (13h)',
                        '1_jour' => '1 Jour',
                        '2_jours_1_nuit' => '2 Jours + 1 Nuit'
                    ];
                ?>
                    <div class="reservation-item">
                        <div class="reservation-info">
                            <div class="reservation-date">
                                <?php echo strftime('%A %d %B %Y', $date->getTimestamp()); ?>
                            </div>
                            <div class="reservation-details">
                                <i class="fas fa-star"></i> <?php echo $forfait_labels[$reservation['forfait']]; ?> •
                                <i class="fas fa-users"></i> <?php echo $reservation['nombre_adultes'] + $reservation['nombre_enfants']; ?> personne(s) •
                                <i class="fas fa-euro-sign"></i> <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €
                            </div>
                        </div>
                        <div class="reservation-status status-<?php echo $reservation['statut']; ?>">
                            <?php
                            $statut_labels = [
                                'en_attente' => 'En attente',
                                'confirmee' => 'Confirmée',
                                'annulee' => 'Annulée'
                            ];
                            echo $statut_labels[$reservation['statut']];
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <a href="reservations.php" class="btn-submit" style="margin-top: 1rem;">
                    <i class="fas fa-eye"></i>
                    Voir toutes mes réservations
                </a>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Vous n'avez aucune réservation à venir</p>
                    <a href="../Reservation/reservation.php" class="btn-submit" style="margin-top: 1.5rem; display: inline-flex;">
                        <i class="fas fa-plus-circle"></i>
                        Réserver maintenant
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
