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
    <link rel="stylesheet" href="../../global-nature-zoo.css">
    <link rel="stylesheet" href="../../dashboard-nature.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-green), var(--accent-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 15px rgba(74, 124, 44, 0.3);
        }

        .user-avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .sidebar-header {
            text-align: center;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-menu li {
            margin-bottom: 0.5rem;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: #2d2d2d;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            border-left: 4px solid transparent;
        }

        .nav-menu a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            color: var(--accent-orange);
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(74, 124, 44, 0.15);
            border-left-color: var(--accent-orange);
            color: var(--primary-green);
        }

        .nav-menu a.logout {
            background: linear-gradient(135deg, #8b0000, #a52a2a);
            color: white;
            margin: 2rem 1rem 0 1rem;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.2);
        }

        .nav-menu a.logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
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
            <div>
                <h1>
                    <i class="fas fa-calendar-check"></i>
                    Gestion des Réservations
                </h1>
                <p>Vue d'ensemble des réservations et des visiteurs attendus</p>
            </div>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <h3>Total Réservations</h3>
                <div class="value"><?php echo $totalReservations; ?></div>
            </div>

            <div class="stat-card" style="border-color: #ffa500;">
                <h3><i class="fas fa-clock"></i> En attente</h3>
                <div class="value" style="color: #ffa500;"><?php echo $enAttente; ?></div>
            </div>

            <div class="stat-card" style="border-color: var(--success);">
                <h3><i class="fas fa-check-circle"></i> Confirmées</h3>
                <div class="value" style="color: var(--success);"><?php echo $confirmees; ?></div>
            </div>

            <div class="stat-card" style="border-color: var(--danger);">
                <h3><i class="fas fa-times-circle"></i> Annulées</h3>
                <div class="value" style="color: var(--danger);"><?php echo $annulees; ?></div>
            </div>
        </div>

        <!-- PROCHAINES VISITES -->
        <div style="background: white; border: 2px solid var(--border-light); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-md); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, #4a7c2c, #d97218, #daa520);"></div>

            <h2 style="margin: 0 0 1.5rem 0; display: flex; align-items: center; gap: 0.8rem; color: var(--primary-green);">
                <i class="fas fa-calendar-day" style="color: var(--accent-orange);"></i>
                Visiteurs attendus - 7 prochains jours (<?php echo $totalPersonnesProchains; ?> personnes)
            </h2>

            <?php if (!empty($prochaines_visites)): ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($prochaines_visites as $visite): ?>
                        <?php
                        $date_obj = new DateTime($visite['date_visite']);
                        $date_fr = strftime('%A %d %B %Y', $date_obj->getTimestamp());
                        ?>
                        <div style="background: var(--bg-light); border-left: 4px solid var(--accent-orange); padding: 1.2rem; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 700; font-size: 1.1rem; color: var(--primary-green); margin-bottom: 0.3rem;">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo ucfirst($date_fr); ?>
                                </div>
                                <div style="color: var(--text-muted);"><?php echo $visite['nb_reservations']; ?> réservation(s)</div>
                            </div>
                            <div style="text-align: center; background: white; padding: 1rem 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                                <div style="font-size: 2rem; font-weight: 800; color: var(--accent-orange);"><?php echo $visite['total_personnes']; ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Personnes</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>Aucune visite prévue dans les 7 prochains jours</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- DERNIÈRES RÉSERVATIONS -->
        <div class="table-container">
            <h2 style="color: var(--primary-green); margin-bottom: 1.5rem;">
                <i class="fas fa-list" style="color: var(--accent-orange);"></i>
                Dernières Réservations
            </h2>

            <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOM</th>
                            <th>DATE VISITE</th>
                            <th>FORFAIT</th>
                            <th>VISITEURS</th>
                            <th>PRIX TOTAL</th>
                            <th>STATUT</th>
                            <th>DATE CRÉATION</th>
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
