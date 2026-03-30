<?php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/roles.php';
require_once __DIR__ . '/../../../connexion.php';

// Vérifier que l'utilisateur est Directeur ou Chef d'équipe
require_role(['Directeur', 'Chef_Equipe'], "Accès non autorisé");

$prenom = get_prenom();
$role = get_role();

// Récupération du nombre total d'utilisateurs
$requeteTotal = "SELECT COUNT(*) as total FROM users";
$resultTotal = mysqli_query($conn, $requeteTotal);
$TotalUsers = mysqli_fetch_assoc($resultTotal)['total'];

// Récupération des statistiques par rôle
$directeurs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Directeur'"))['total'];
$chefs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Chef_Equipe'"))['total'];
$veterinaires = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Veterinaire'"))['total'];
$employes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Employe'"))['total'];
$benevoles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Benevole'"))['total'];
$clients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Client'"))['total'];

// Récupération des utilisateurs pour affichage
$requete = "SELECT * FROM users ORDER BY date_creation DESC LIMIT 0, 30;";
$resultat = mysqli_query($conn, $requete);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Zoo Paradis</title>
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
                <i class="fas fa-<?php echo $role === 'Directeur' ? 'crown' : 'user-tie'; ?>"></i>
            </div>
            <h2><?php echo htmlspecialchars($prenom); ?></h2>
            <p><?php echo $role === 'Directeur' ? 'Directeur' : "Chef d'équipe"; ?></p>
        </div>

        <ul class="nav-menu">
            <li><a href="../../main.php"><i class="fas fa-home"></i> Tableau de bord</a></li>
            <li><a href="../../animaux/dashboard/dashboard.php"><i class="fas fa-paw"></i> Animaux</a></li>
            <li><a href="../../especes/Dashboard/dashboard.php"><i class="fas fa-dna"></i> Espèces</a></li>
            <li><a href="../../enclos/dashboard.php"><i class="fas fa-warehouse"></i> Enclos</a></li>
            <li><a href="dashboard.php" class="active"><i class="fas fa-user-cog"></i> Gestion Comptes</a></li>

            <?php if ($role === 'Directeur'): ?>
            <li><a href="../../reservations/dashboard.php"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <?php endif; ?>

            <?php if ($role === 'Directeur' || $role === 'Chef_Equipe'): ?>
            <li><a href="../../gestion_comptes/creer_compte.php"><i class="fas fa-user-plus"></i> Créer un compte</a></li>
            <?php endif; ?>

            <li><a href="../../../Connexion/deconnexion.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>
                    <i class="fas fa-user-cog"></i>
                    Gestion des Comptes
                </h1>
                <p>Gestion complète de tous les utilisateurs et du personnel</p>
            </div>
            <?php if ($role === 'Directeur' || $role === 'Chef_Equipe'): ?>
            <a href="../../gestion_comptes/creer_compte.php" class="btn-add">
                <i class="fas fa-user-plus"></i>
                Créer un compte
            </a>
            <?php endif; ?>
        </div>

        <!-- Actions rapides -->
        <div class="action-buttons">
            <a href="../modifier/modifier.php">
                <i class="fas fa-edit"></i>
                Modifier
            </a>
            <a href="../recherche/recherche_new.php">
                <i class="fas fa-search"></i>
                Rechercher
            </a>
        </div>

        <!-- STATISTIQUES -->
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="stat-card">
                <h3><i class="fas fa-user-cog"></i> Total Comptes</h3>
                <div class="value"><?php echo $TotalUsers; ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-crown"></i> Directeurs</h3>
                <div class="value"><?php echo $directeurs; ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-user-tie"></i> Chefs d'équipe</h3>
                <div class="value"><?php echo $chefs; ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-stethoscope"></i> Vétérinaires</h3>
                <div class="value"><?php echo $veterinaires; ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-user"></i> Employés</h3>
                <div class="value"><?php echo $employes; ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-hands-helping"></i> Bénévoles</h3>
                <div class="value"><?php echo $benevoles; ?></div>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-user-circle"></i> Clients</h3>
                <div class="value"><?php echo $clients; ?></div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RÔLE</th>
                        <th>NOM</th>
                        <th>PRÉNOM</th>
                        <th>EMAIL</th>
                        <th>TÉLÉPHONE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($enreg = mysqli_fetch_array($resultat)) { ?>
                        <tr>
                            <td><?php echo $enreg["id"]; ?></td>
                            <td>
                                <span class="role-badge role-<?php
                                    echo match($enreg["role"]) {
                                        'Directeur' => 'directeur',
                                        'Chef_Equipe' => 'chef',
                                        'Veterinaire' => 'veterinaire',
                                        'Employe' => 'employe',
                                        'Benevole' => 'benevole',
                                        'Client' => 'client',
                                        default => 'employe'
                                    };
                                ?>">
                                    <?php
                                        echo match($enreg["role"]) {
                                            'Directeur' => '<i class="fas fa-crown"></i> Directeur',
                                            'Chef_Equipe' => '<i class="fas fa-user-tie"></i> Chef d\'équipe',
                                            'Veterinaire' => '<i class="fas fa-stethoscope"></i> Vétérinaire',
                                            'Employe' => '<i class="fas fa-user"></i> Employé',
                                            'Benevole' => '<i class="fas fa-hands-helping"></i> Bénévole',
                                            'Client' => '<i class="fas fa-user-circle"></i> Client',
                                            default => $enreg["role"]
                                        };
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($enreg["nom"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["prenom"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($enreg["email"]); ?></td>
                            <td><?php echo htmlspecialchars($enreg["telephone"] ?? ''); ?></td>
                            <td>
                                <form method="POST" action="supprimer.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $enreg['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php mysqli_close($conn); ?>
