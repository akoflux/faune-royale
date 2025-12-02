<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../connexion.php';

// Vérifier que l'utilisateur est Employé, Vétérinaire ou Bénévole
require_role(['Employe', 'Veterinaire', 'Benevole'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupérer les statistiques
$requeteTotal = "SELECT COUNT(*) as total FROM especes";
$resultTotal = mysqli_query($conn, $requeteTotal);
$totalEspeces = mysqli_fetch_assoc($resultTotal)['total'];

$requeteTotal = "SELECT COUNT(*) as total FROM animaux";
$resultTotal = mysqli_query($conn, $requeteTotal);
$totalAnimaux = mysqli_fetch_assoc($resultTotal)['total'];

// Statistiques supplémentaires pour Animaux
$animauxAquatiques = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM animaux a JOIN especes e ON a.Espece = e.nom_race WHERE e.animal_aquatique = 'Oui'"))['total'];
$animauxTerrestres = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM animaux a JOIN especes e ON a.Espece = e.nom_race WHERE e.animal_aquatique = 'Non'"))['total'];

// Espèces par habitat
$especesAquatiques = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM especes WHERE animal_aquatique = 'Oui'"))['total'];
$especesTerrestres = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM especes WHERE animal_aquatique = 'Non'"))['total'];

// Récupérer tous les enclos depuis la base de données
$requeteEnclos = "SELECT Nom FROM enclos ORDER BY Nom";
$resultEnclos = mysqli_query($conn, $requeteEnclos);

$enclosList = [];
while ($row = mysqli_fetch_assoc($resultEnclos)) {
    $enclosList[] = $row['Nom'];
}

// Animaux par enclos (dynamique)
$animauxParEnclos = [];
foreach ($enclosList as $enclosNom) {
    $req = "SELECT Nom FROM animaux WHERE Enclos = ?";
    $stmt = mysqli_prepare($conn, $req);
    mysqli_stmt_bind_param($stmt, "s", $enclosNom);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $animaux = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $animaux[] = $row['Nom'];
    }
    $animauxParEnclos[$enclosNom] = $animaux;
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Zoo Paradis</title>
    <link rel="stylesheet" href="../global-futuriste.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
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

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            width: calc(100% - 280px);
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--text-muted);
        }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 212, 255, 0.3);
            border-color: var(--primary);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-card .icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stat-card h3 {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ENCLOS SECTION */
        .enclos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .enclos-card {
            background: rgba(22, 33, 62, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 1.5rem;
        }

        .enclos-card h3 {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .enclos-card h3 i {
            font-size: 1.5rem;
        }

        .animaux-list {
            color: var(--text-muted);
            line-height: 1.8;
        }

        .empty-enclos {
            color: var(--text-muted);
            font-style: italic;
            opacity: 0.5;
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
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-<?php
                    echo match($role) {
                        'Veterinaire' => 'stethoscope',
                        'Benevole' => 'hands-helping',
                        default => 'user'
                    };
                ?>"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p><?php
                echo match($role) {
                    'Veterinaire' => 'Vétérinaire',
                    'Benevole' => 'Bénévole',
                    default => 'Employé'
                };
            ?></p>
        </div>

        <ul class="nav-menu">
            <li><a href="main.php" class="active"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="animaux/dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>

            <li><a href="../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>Tableau de bord</h1>
            <p>Vue d'ensemble de votre zoo</p>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-dna"></i>
                </div>
                <h3>Espèces Totales</h3>
                <div class="value"><?php echo $totalEspeces; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-paw"></i>
                </div>
                <h3>Animaux Totaux</h3>
                <div class="value"><?php echo $totalAnimaux; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-water"></i>
                </div>
                <h3>Animaux Aquatiques</h3>
                <div class="value" style="background: linear-gradient(135deg, #0095ff, #00d4ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $animauxAquatiques; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background: linear-gradient(135deg, #8B4513, #d2691e);">
                    <i class="fas fa-mountain"></i>
                </div>
                <h3>Animaux Terrestres</h3>
                <div class="value" style="background: linear-gradient(135deg, #d2691e, #daa520); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $animauxTerrestres; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background: linear-gradient(135deg, #0095ff, #00d4ff);">
                    <i class="fas fa-fish"></i>
                </div>
                <h3>Espèces Aquatiques</h3>
                <div class="value" style="background: linear-gradient(135deg, #0095ff, #00d4ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $especesAquatiques; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background: linear-gradient(135deg, #228B22, #32CD32);">
                    <i class="fas fa-tree"></i>
                </div>
                <h3>Espèces Terrestres</h3>
                <div class="value" style="background: linear-gradient(135deg, #228B22, #32CD32); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $especesTerrestres; ?></div>
            </div>
        </div>

        <!-- ENCLOS -->
        <h2 style="margin: 2rem 0 1.5rem; display: flex; align-items: center; gap: 0.8rem;">
            <i class="fas fa-home" style="color: var(--primary);"></i>
            État des enclos
        </h2>

        <div class="enclos-grid">
            <?php foreach ($enclosList as $enclosNom): ?>
                <div class="enclos-card">
                    <h3>
                        <i class="fas fa-warehouse"></i>
                        <?php echo htmlspecialchars($enclosNom); ?>
                    </h3>
                    <?php if (!empty($animauxParEnclos[$enclosNom])): ?>
                        <div class="animaux-list">
                            <?php echo htmlspecialchars(implode(", ", $animauxParEnclos[$enclosNom])); ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-enclos">Aucun animal</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
