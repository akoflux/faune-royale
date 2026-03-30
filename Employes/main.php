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
    <link rel="stylesheet" href="../global-nature-zoo.css">
    <link rel="stylesheet" href="../dashboard-nature.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles spécifiques à la page Employés */
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

        .enclos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .enclos-card {
            background: white;
            border: 2px solid rgba(139, 69, 19, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(62, 39, 35, 0.1);
            position: relative;
            overflow: hidden;
        }

        .enclos-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4a7c2c, #d97218);
        }

        .enclos-card h3 {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            color: var(--primary-green);
        }

        .enclos-card h3 i {
            font-size: 1.5rem;
            color: var(--accent-orange);
        }

        .animaux-list {
            color: #6b5d52;
            line-height: 1.8;
        }

        .empty-enclos {
            color: #6b5d52;
            font-style: italic;
            opacity: 0.5;
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
                <div class="value"><?php echo $animauxAquatiques; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-mountain"></i>
                </div>
                <h3>Animaux Terrestres</h3>
                <div class="value"><?php echo $animauxTerrestres; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-fish"></i>
                </div>
                <h3>Espèces Aquatiques</h3>
                <div class="value"><?php echo $especesAquatiques; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-tree"></i>
                </div>
                <h3>Espèces Terrestres</h3>
                <div class="value"><?php echo $especesTerrestres; ?></div>
            </div>
        </div>

        <!-- ENCLOS -->
        <h2 style="margin: 2rem 0 1.5rem; display: flex; align-items: center; gap: 0.8rem;">
            <i class="fas fa-home" style="color: var(--accent-orange);"></i>
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
