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
    <title>Gestion des Enclos - Faune Royal</title>
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
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card">
                <h3><i class="fas fa-warehouse"></i> Total Enclos</h3>
                <div class="value"><?php echo count($enclos_list); ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-paw"></i> Animaux logés</h3>
                <div class="value"><?php echo array_sum(array_map('count', $enclos_data)); ?></div>
            </div>

            <div class="stat-card" style="<?php echo $animaux_sans_enclos > 0 ? 'border-color: var(--warning);' : ''; ?>">
                <h3><i class="fas fa-exclamation-triangle"></i> Sans enclos</h3>
                <div class="value" style="<?php echo $animaux_sans_enclos > 0 ? 'color: var(--warning);' : ''; ?>"><?php echo $animaux_sans_enclos; ?></div>
            </div>
        </div>

        <!-- ALERT SI ANIMAUX SANS ENCLOS -->
        <?php if ($animaux_sans_enclos > 0): ?>
        <div class="alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Attention !</strong> <?php echo $animaux_sans_enclos; ?> animau<?php echo $animaux_sans_enclos > 1 ? 'x' : ''; ?> sans enclos.
                <a href="../animaux/dashboard/dashboard.php">Voir les animaux</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- LISTE DES ENCLOS -->
        <div class="enclos-grid">
            <?php if (empty($enclos_list)): ?>
                <div class="enclos-card" style="grid-column: 1 / -1;">
                    <div class="empty-enclos">
                        <i class="fas fa-warehouse"></i>
                        <p>Aucun enclos créé pour le moment</p>
                        <p>Cliquez sur "Créer un enclos" pour commencer</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($enclos_data as $enclos_nom => $animaux): ?>
                    <div class="enclos-card">
                        <h3>
                            <i class="fas fa-warehouse"></i>
                            <?php echo htmlspecialchars($enclos_nom); ?>
                            <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">
                                (<?php echo count($animaux); ?> animaux)
                            </span>
                            <?php if (empty($animaux)): ?>
                                <form method="POST" action="supprimer.php" style="display: inline; margin-left: auto;">
                                    <input type="hidden" name="nom_enclos" value="<?php echo htmlspecialchars($enclos_nom); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enclos vide ?')" title="Supprimer l'enclos">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </h3>

                        <?php if (empty($animaux)): ?>
                            <div class="empty-enclos">
                                <i class="fas fa-inbox" style="font-size: 2.5rem; opacity: 0.2; margin-bottom: 0.5rem;"></i>
                                <p style="margin: 0;">Enclos vide</p>
                            </div>
                        <?php else: ?>
                            <ul class="animal-list">
                                <?php foreach ($animaux as $animal): ?>
                                    <li class="animal-item">
                                        <div>
                                            <div class="animal-name">
                                                <i class="fas fa-paw" style="color: var(--accent-orange); font-size: 0.9rem;"></i>
                                                <?php echo htmlspecialchars($animal['Nom']); ?>
                                                <span class="habitat-badge <?php echo $animal['animal_aquatique'] === 'Oui' ? 'habitat-aquatique' : 'habitat-terrestre'; ?>">
                                                    <?php echo $animal['animal_aquatique'] === 'Oui' ? 'Aquatique' : 'Terrestre'; ?>
                                                </span>
                                            </div>
                                            <div class="animal-info" style="margin-top: 0.3rem;">
                                                <i class="fas fa-dna"></i> <?php echo htmlspecialchars($animal['Espece']); ?>
                                            </div>
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
