<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../connexion.php';

// IMPORTANT: Seul le Directeur peut accéder aux réservations
require_role(['Directeur'], "Accès non autorisé - Réservé au Directeur");

$prenom = get_prenom();
$role = get_role();

// Récupération du nombre total de réservations
$requeteTotal = "SELECT COUNT(*) as total FROM reservations";
$resultTotal = mysqli_query($conn, $requeteTotal);
$totalReservations = mysqli_fetch_assoc($resultTotal)['total'];

// Récupération des statistiques par statut
$enAttente = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reservations WHERE statut = 'en_attente'"))['total'];
$confirmees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reservations WHERE statut = 'confirmee'"))['total'];
$annulees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reservations WHERE statut = 'annulee'"))['total'];

// Récupération des personnes attendues dans les 7 prochains jours
$date_debut = date('Y-m-d');
$date_fin = date('Y-m-d', strtotime('+7 days'));

$requeteProchains = "
    SELECT
        date_visite,
        SUM(nombre_adultes + nombre_enfants) as total_personnes,
        COUNT(*) as nb_reservations
    FROM reservations
    WHERE date_visite BETWEEN ? AND ?
    AND statut IN ('en_attente', 'confirmee')
    GROUP BY date_visite
    ORDER BY date_visite ASC
";
$stmtProchains = $conn->prepare($requeteProchains);
$stmtProchains->bind_param("ss", $date_debut, $date_fin);
$stmtProchains->execute();
$resultProchains = $stmtProchains->get_result();

// Calculer le total des personnes attendues dans les 7 prochains jours
$totalPersonnesProchains = 0;
$prochaines_visites = [];
while ($row = $resultProchains->fetch_assoc()) {
    $totalPersonnesProchains += $row['total_personnes'];
    $prochaines_visites[] = $row;
}
$stmtProchains->close();

// Récupération des dernières réservations (30 dernières)
$requete = "
    SELECT r.*, u.role
    FROM reservations r
    LEFT JOIN users u ON r.user_id = u.id
    ORDER BY r.date_creation DESC
    LIMIT 0, 30
";
$resultat = mysqli_query($conn, $requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - Zoo Paradis</title>
    <link rel="stylesheet" href="../../global-futuriste.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: rgba(22, 33, 62, 0.8);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 30px rgba(0, 212, 255, 0.4);
        }

        .user-avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .sidebar-header h2 {
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }

        .sidebar-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .nav-menu li {
            margin-bottom: 0.5rem;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.2rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-menu a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            color: var(--primary);
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(0, 212, 255, 0.1);
            border-left: 3px solid var(--primary);
            padding-left: calc(1.2rem - 3px);
        }

        .nav-menu a.logout {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid var(--danger);
            margin-top: 2rem;
        }

        .nav-menu a.logout:hover {
            background: rgba(255, 0, 110, 0.2);
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            width: calc(100% - 280px);
        }

        .page-header {
            margin-bottom: 2rem;
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header h1 i {
            color: var(--primary);
        }

        .page-header p {
            color: var(--text-muted);
        }

        /* STATS */
        .stat-box {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
            border-color: var(--primary);
        }

        .stat-box h3 {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-box .value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* PROCHAINES VISITES */
        .prochaines-visites {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .prochaines-visites h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .prochaines-visites h2 i {
            color: var(--success);
        }

        .visite-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .visite-item:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
        }

        .visite-date {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .visite-date i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .visite-date-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .visite-date-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .visite-stats {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .visite-stat {
            text-align: center;
        }

        .visite-stat .number {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--success), #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .visite-stat .label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        /* TABLE */
        .table-container {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            overflow-x: auto;
        }

        .table-container h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .table-container h2 i {
            color: var(--primary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        td {
            color: var(--text);
        }

        tr:hover {
            background: rgba(0, 212, 255, 0.05);
        }

        .statut-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .statut-en_attente {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .statut-confirmee {
            background: rgba(6, 255, 165, 0.2);
            color: var(--success);
            border: 1px solid rgba(6, 255, 165, 0.3);
        }

        .statut-annulee {
            background: rgba(255, 0, 110, 0.2);
            color: var(--danger);
            border: 1px solid rgba(255, 0, 110, 0.3);
        }

        .forfait-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(123, 44, 191, 0.2);
            color: var(--secondary);
            border: 1px solid rgba(123, 44, 191, 0.3);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        @media (max-width: 968px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .visite-stats {
                flex-direction: column;
                gap: 1rem;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-crown"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p>Directeur</p>
        </div>

        <ul class="nav-menu">
            <li><a href="../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../animaux/dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>
            <li><a href="../enclos/dashboard.php"><i class="fas fa-warehouse"></i> Enclos</a></li>
            <li><a href="../user/dashboard/dashboard.php"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <li><a href="../gestion_comptes/creer_compte.php"><i class="fas fa-user-plus"></i> Créer un compte</a></li>
            <li><a href="../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>
                <i class="fas fa-calendar-check"></i>
                Gestion des Réservations
            </h1>
            <p>Vue d'ensemble des réservations et des visiteurs attendus</p>
        </div>

        <!-- STATISTIQUES -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-box" style="margin-bottom: 0;">
                <h3>Total Réservations</h3>
                <div class="value"><?php echo $totalReservations; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-clock"></i> En attente</h3>
                <div class="value" style="background: linear-gradient(135deg, #ffc107, #ffed4e); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $enAttente; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-check-circle"></i> Confirmées</h3>
                <div class="value" style="background: linear-gradient(135deg, var(--success), #00ff88); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $confirmees; ?></div>
            </div>

            <div class="stat-box" style="margin-bottom: 0;">
                <h3><i class="fas fa-times-circle"></i> Annulées</h3>
                <div class="value" style="background: linear-gradient(135deg, var(--danger), #ff2266); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $annulees; ?></div>
            </div>
        </div>

        <!-- PROCHAINES VISITES -->
        <div class="prochaines-visites">
            <h2>
                <i class="fas fa-calendar-day"></i>
                Visiteurs attendus - 7 prochains jours (<?php echo $totalPersonnesProchains; ?> personnes)
            </h2>

            <?php if (!empty($prochaines_visites)): ?>
                <?php foreach ($prochaines_visites as $visite): ?>
                    <?php
                    $date_obj = new DateTime($visite['date_visite']);
                    $date_fr = strftime('%A %d %B %Y', $date_obj->getTimestamp());
                    ?>
                    <div class="visite-item">
                        <div class="visite-date">
                            <i class="fas fa-calendar"></i>
                            <div class="visite-date-info">
                                <h4><?php echo ucfirst($date_fr); ?></h4>
                                <p><?php echo $visite['nb_reservations']; ?> réservation(s)</p>
                            </div>
                        </div>
                        <div class="visite-stats">
                            <div class="visite-stat">
                                <div class="number"><?php echo $visite['total_personnes']; ?></div>
                                <div class="label">Personnes</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Aucune visite prévue dans les 7 prochains jours</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- DERNIÈRES RÉSERVATIONS -->
        <div class="table-container">
            <h2>
                <i class="fas fa-list"></i>
                Dernières Réservations
            </h2>

            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Date Visite</th>
                            <th>Forfait</th>
                            <th>Visiteurs</th>
                            <th>Prix Total</th>
                            <th>Statut</th>
                            <th>Date Création</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($resultat)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['prenom'] . ' ' . $row['nom']); ?></td>
                                <td>
                                    <?php
                                    $date_obj = new DateTime($row['date_visite']);
                                    echo $date_obj->format('d/m/Y');
                                    ?>
                                </td>
                                <td>
                                    <span class="forfait-badge">
                                        <?php
                                        echo match($row['forfait']) {
                                            'demi_journee' => 'Demi-journée',
                                            '1_jour' => '1 Jour',
                                            '2_jours_1_nuit' => '2J/1N',
                                            default => $row['forfait']
                                        };
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-user"></i> <?php echo $row['nombre_adultes']; ?> adulte(s)
                                    <br>
                                    <i class="fas fa-child"></i> <?php echo $row['nombre_enfants']; ?> enfant(s)
                                </td>
                                <td><strong><?php echo number_format($row['prix_total'], 2); ?> €</strong></td>
                                <td>
                                    <span class="statut-badge statut-<?php echo $row['statut']; ?>">
                                        <?php
                                        echo match($row['statut']) {
                                            'en_attente' => 'En attente',
                                            'confirmee' => 'Confirmée',
                                            'annulee' => 'Annulée',
                                            default => $row['statut']
                                        };
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $date_creation = new DateTime($row['date_creation']);
                                    echo $date_creation->format('d/m/Y H:i');
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Aucune réservation trouvée</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
