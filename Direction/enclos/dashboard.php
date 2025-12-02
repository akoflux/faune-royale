<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../connexion.php';

// Vérifier que l'utilisateur est Directeur ou Chef d'équipe
require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupérer TOUS les enclos depuis la table enclos (y compris les vides)
$requeteEnclos = "SELECT Nom FROM enclos ORDER BY Nom";
$resultEnclos = mysqli_query($conn, $requeteEnclos);

$enclos_list = [];
while ($row = mysqli_fetch_assoc($resultEnclos)) {
    $enclos_list[] = $row['Nom'];
}

// Pour chaque enclos, récupérer les animaux (s'il y en a)
$enclos_data = [];
foreach ($enclos_list as $enclos_nom) {
    $req = "SELECT a.Nom, a.Espece, a.Sexe, a.date_naissance, e.animal_aquatique, Nom, Espece
            FROM animaux a
            JOIN especes e ON a.Espece = e.nom_race
            WHERE a.Enclos = ?";
    $stmt = $conn->prepare($req);
    $stmt->bind_param("s", $enclos_nom);
    $stmt->execute();
    $result = $stmt->get_result();

    $animaux = [];
    while ($animal = $result->fetch_assoc()) {
        $animaux[] = $animal;
    }

    // Ajouter l'enclos même s'il est vide
    $enclos_data[$enclos_nom] = $animaux;
    $stmt->close();
}

// Compter les animaux sans enclos
$animaux_sans_enclos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM animaux WHERE Enclos IS NULL OR Enclos = ''"))['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Enclos - Zoo Paradis</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn-add {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--success), #00ff88);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(6, 255, 165, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(6, 255, 165, 0.5);
        }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
            border-color: var(--primary);
        }

        .stat-card h3 {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ENCLOS GRID */
        .enclos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }

        .enclos-card {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .enclos-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 212, 255, 0.3);
            border-color: var(--primary);
        }

        .enclos-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .enclos-card h3 i {
            color: var(--success);
        }

        .animal-list {
            list-style: none;
        }

        .animal-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.8rem;
            transition: all 0.3s ease;
        }

        .animal-item:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
        }

        .animal-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .animal-info {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .animal-info i {
            margin-right: 0.3rem;
            color: var(--primary);
        }

        .habitat-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .habitat-aquatique {
            background: rgba(0, 149, 255, 0.2);
            color: #0095ff;
            border: 1px solid rgba(0, 149, 255, 0.3);
        }

        .habitat-terrestre {
            background: rgba(139, 69, 19, 0.2);
            color: #d2691e;
            border: 1px solid rgba(139, 69, 19, 0.3);
        }

        .btn-delete {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
            transform: scale(1.05);
        }

        .empty-enclos {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-style: italic;
        }

        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-warning i {
            font-size: 2rem;
            color: #ffc107;
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

            .enclos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-<?php echo $role === 'Directeur' ? 'crown' : 'user-tie'; ?>"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p><?php echo $role === 'Directeur' ? 'Directeur' : "Chef d'équipe"; ?></p>
        </div>

        <ul class="nav-menu">
            <li><a href="../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../animaux/dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-warehouse"></i> Enclos</a></li>
            <li><a href="../user/dashboard/dashboard.php"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>

            <?php if ($role === 'Directeur'): ?>
            <li><a href="../reservations/dashboard.php"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <?php endif; ?>

            <?php if ($role === 'Directeur' || $role === 'Chef_Equipe'): ?>
            <li><a href="../gestion_comptes/creer_compte.php"><i class="fas fa-user-plus"></i> Créer un compte</a></li>
            <?php endif; ?>

            <li><a href="../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-warehouse"></i>
                    Gestion des Enclos
                </h1>
                <p>Organisez et gérez les enclos de votre zoo</p>
            </div>
            <a href="creer.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Créer un enclos
            </a>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Enclos</h3>
                <div class="value"><?php echo count($enclos_list); ?></div>
            </div>

            <div class="stat-card">
                <h3>Animaux logés</h3>
                <div class="value"><?php echo array_sum(array_map('count', $enclos_data)); ?></div>
            </div>

            <div class="stat-card">
                <h3>Sans enclos</h3>
                <div class="value" style="background: linear-gradient(135deg, #ffc107, #ffed4e); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $animaux_sans_enclos; ?></div>
            </div>
        </div>

        <!-- ALERT SI ANIMAUX SANS ENCLOS -->
        <?php if ($animaux_sans_enclos > 0): ?>
        <div class="alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Attention !</strong> <?php echo $animaux_sans_enclos; ?> animau<?php echo $animaux_sans_enclos > 1 ? 'x' : ''; ?> sans enclos.
                <a href="../animaux/dashboard/dashboard.php" style="color: var(--primary); text-decoration: underline;">Voir les animaux</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- LISTE DES ENCLOS -->
        <div class="enclos-grid">
            <?php if (empty($enclos_list)): ?>
                <div class="enclos-card" style="grid-column: 1 / -1;">
                    <div class="empty-enclos">
                        <i class="fas fa-warehouse" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>Aucun enclos créé pour le moment</p>
                        <p>Cliquez sur "Créer un enclos" pour commencer</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($enclos_data as $enclos_nom => $animaux): ?>
                    <div class="enclos-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="margin: 0;">
                                <i class="fas fa-warehouse"></i>
                                <?php echo htmlspecialchars($enclos_nom); ?>
                                <span style="font-size: 0.9rem; color: var(--text-muted);">
                                    (<?php echo count($animaux); ?> animau<?php echo count($animaux) > 1 ? 'x' : ''; ?>)
                                </span>
                            </h3>
                            <?php if (empty($animaux)): ?>
                                <form method="POST" action="supprimer.php" style="display: inline;">
                                    <input type="hidden" name="nom_enclos" value="<?php echo htmlspecialchars($enclos_nom); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enclos vide ?')" title="Supprimer l'enclos">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($animaux)): ?>
                            <div class="empty-enclos">
                                <p>Enclos vide</p>
                            </div>
                        <?php else: ?>
                            <ul class="animal-list">
                                <?php foreach ($animaux as $animal): ?>
                                    <li class="animal-item">
                                        <div class="animal-name">
                                            <?php echo htmlspecialchars($animal['Nom']); ?>
                                            <span class="habitat-badge <?php echo $animal['animal_aquatique'] === 'Oui' ? 'habitat-aquatique' : 'habitat-terrestre'; ?>">
                                                <?php echo $animal['animal_aquatique'] === 'Oui' ? 'Aquatique' : 'Terrestre'; ?>
                                            </span>
                                        </div>
                                        <div class="animal-info">
                                            <i class="fas fa-dna"></i><?php echo htmlspecialchars($animal['Nom']); ?>
                                            <br>
                                            <i class="fas fa-layer-group"></i><?php echo htmlspecialchars($animal['Espece']); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
