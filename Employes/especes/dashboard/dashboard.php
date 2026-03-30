<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est Employé, Vétérinaire ou Bénévole
require_role(['Employe', 'Veterinaire', 'Benevole'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération du nombre total d'espèces
$requeteTotal = "SELECT COUNT(*) as total FROM especes";
$resultTotal = mysqli_query($conn, $requeteTotal);
$totalEspeces = mysqli_fetch_assoc($resultTotal)['total'];

// Récupération des espèces pour affichage
$requete = "SELECT * FROM especes ORDER BY id DESC LIMIT 0, 30;";
$resultat = mysqli_query($conn, $requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Espèces - Zoo Paradis</title>
    <link rel="stylesheet" href="../../../global-nature-zoo.css">
    <link rel="stylesheet" href="../../../dashboard-nature.css">
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
            <li><a href="../../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../../animaux/Dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-dna"></i> Espèces</a></li>

            <li><a href="../../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-dna"></i>
                    Gestion des espèces
                </h1>
                <p>Liste complète des espèces du zoo</p>
            </div>
            <?php if ($role !== 'Benevole'): ?>
            <a href="../ajouter/ajouter.php" class="btn-add">
                <i class="fas fa-plus"></i>
                Ajouter une espèce
            </a>
            <?php endif; ?>
        </div>

        <?php if ($role !== 'Benevole'): ?>
        <div class="action-buttons">
            <a href="../modifier/modifier.php">
                <i class="fas fa-edit"></i>
                Modifier
            </a>
            <a href="../recherche/recherche.php">
                <i class="fas fa-search"></i>
                Rechercher
            </a>
        </div>
        <?php else: ?>
        <div class="action-buttons">
            <a href="../recherche/recherche.php">
                <i class="fas fa-search"></i>
                Rechercher
            </a>
        </div>
        <?php endif; ?>

        <div class="stat-box">
            <h3>Total Espèces</h3>
            <div class="value"><?php echo $totalEspeces; ?></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de race</th>
                        <th>Type nourriture</th>
                        <th>Durée de vie</th>
                        <th>Animal aquatique</th>
                        <?php if ($role !== 'Benevole'): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($enreg = mysqli_fetch_array($resultat)) { ?>
                        <tr>
                            <td><?php echo $enreg["id"]; ?></td>
                            <td><?php echo htmlspecialchars($enreg["nom_race"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["type_nourriture"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["duree_vie"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["animal_aquatique"] ?? ''); ?></td>
                            <?php if ($role !== 'Benevole'): ?>
                            <td>
                                <form method="POST" action="supprimer.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $enreg['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette espèce ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
